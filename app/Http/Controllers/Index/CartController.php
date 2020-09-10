<?php

namespace App\Http\Controllers\Index;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Cart;
use App\Model\ShopGoods;
use App\Model\Order;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartController extends Controller
{
    public function checkLogin(){
        $user=session('user');
        $user_id=$user['id'];
        return $user_id;
    }
    //加入购物车
    public function addCart(Request $request){
        //接收商品id
        $goods_id=$request->input("goods_id");
        //接收购买数量
        $buy_number=$request->input("buy_number");
        $goodsInfo=ShopGoods::find($goods_id);
        if($goodsInfo == NULL)
        {
            echo json_encode(['code'=>false,'font'=>'添加失败']);
        }
        // 判断用户是否登录  检测是否有，登陆时的存储session信息
        $user_id=$this->checkLogin();
        if(!empty($user_id)){
            //加入购物车  数据库
            $res=$this->addCartDb($goods_id,$buy_number);
        }else{
            //加入购物车  session
            $res=$this->addCartCookie($goods_id,$buy_number);
        }
        if($res=='bbb'){
            echo json_encode(['code'=>false,'font'=>'添加失败']);
        }else{
            echo json_encode(['code'=>true]);
        }
    }

    //加入购物车  数据库
    public function addCartDb($goods_id,$buy_number){
        //接收用户id
        $user=session('user');
        $user_id=$user['id'];
        //where条件
        $where=[
            ['goods_id','=',$goods_id],
            ['user_id','=',$user_id],
            ['is_del','=',1]
        ];
        //在购物车表  查一条数据
        $cartInfo=Cart::where($where)->first();
        //查询库存 
        $goods_num=ShopGoods::where('goods_id',$goods_id)->value('goods_num');
        //判断查询购物车的这条数据存在时
        if(!empty($cartInfo)){
            $num=$cartInfo['buy_number']+$buy_number;
            //检测库存
            //如果购物车里的数量+点击的数量>库存
            if($num>$goods_num){
                //那么点击的数量=库存
                $num=$goods_num;
            }
            //购买数量累加
            $res=Cart::where($where)->update(['buy_number'=>$num,'add_time'=>time()]);
            if($res){
                return 'aaa';
            }else{
                return 'bbb';
            }
        }else{
            //检测库存
            if($buy_number>$goods_num){
                $buy_number=$goods_num;
            }
            //添加数据
            //把商品id、购买数量、加入时间、用户id存入数据库
            $info=['goods_id'=>$goods_id,'buy_number'=>$buy_number,'add_time'=>time(),'user_id'=>$user_id];
            $res=Cart::insert($info);
            if($res){
                return 'aaa';
            }else{
                return 'bbb';
            }
        }
    }
    //加入购物车  session
    public function addCartCookie($goods_id,$buy_number){
        $cartInfo=session('cartInfo');
        if(empty($cartInfo)){
            $cartInfo=[];
        }
        //查询库存
        $goods_num=ShopGoods::where('goods_id',$goods_id)->value('goods_num');
        if(array_key_exists($goods_id,$cartInfo)){
            //检测库存
            //如果购物车里的数量+点击的数量>库存
            if(($cartInfo[$goods_id]['buy_number']+$buy_number)>$goods_num){
                //那么点击的数量=库存
                $num=$goods_num;
            }else{
                //否则购物车里的数量+点击的数量
                $num=$cartInfo[$goods_id]['buy_number']+$buy_number;
            }
            //累加
            $cartInfo[$goods_id]['buy_number']=$num;
            $cartInfo[$goods_id]['add_time']=time();
        }else{
            //检测库存
            if($buy_number>$goods_num){
                $buy_number=$goods_num;
            }
            //添加
            $cartInfo[$goods_id]=['goods_id'=>$goods_id,'buy_number'=>$buy_number,'add_time'=>time()];
        }
        session(['cartInfo'=>$cartInfo]);
    }
    //购物车列表
    public function cartList(){
        $user_id=$this->checkLogin();
        //判断是否登录
        if(!empty($user_id)){
            //取出购物车数据  数据库
            $cartInfo=$this->getCartDb();
        }else{
            //取出购物车数据  cookie
            $cartInfo=$this->getCartCookie();
        }
        $money=0;
        foreach($cartInfo as $k=>$v){
            $money+=$v['goods_price']*$v['buy_number'];
        }
        return view("index.cart.cart",["cartInfo"=>$cartInfo,'money'=>$money]);
    }
    //取出购物车数据  数据库
    public function getCartDb(){
        //两表联查  商品表  购物车表
        //获取用户id
        $user=session('user');
        $user_id=$user['id'];
        //where条件  用户id  购物未删除
        $where=[
            ['shop_cart.user_id','=',$user_id],
            ['is_del','=',1]
        ];
        //根据两表里共有的商品id  查找商品id，购买数量，商品价格，商品库存
        $cartInfo=Cart::leftjoin('shop_goods',"shop_cart.goods_id","=","shop_goods.goods_id")
            ->where($where)
            ->get();
        return $cartInfo->toArray();
    }
    //取出购物车数据  session
    public function getCartCookie(){
        $cartInfo=session('cartInfo');
        if(!empty($cartInfo)){
            //循环处理
            foreach($cartInfo as $k=>$v){
                //根据cookie的商品id查询商品表
                $info=ShopGoods::where("goods_id",$v["goods_id"])->first();
                //将对象转换数组 toArray
                $info=$info->toArray();
                //将两个数组合并成一个数组array_merge
                $cartInfo[$k]=array_merge($v,$info);
            }
            return $cartInfo;
        }else{
            return $cartInfo=[];
        }
    }
    //更改购买数据
    public function changeNumber(Request $request){
        $goods_id=$request->input("goods_id");
        $buy_number=$request->input("buy_number");
        $user_id=$this->checkLogin();
        if(!empty($user_id)){
            //更改购买数量  数据库
            $res=$this->changeNumberDb($goods_id,$buy_number);
        }else{
            //更改购买数量  cookie
            $res=$this->changeNumberCookie($goods_id,$buy_number);
        }
        if($res===false){
            echo json_encode(['code'=>1,'font'=>'失败']);
        }else{
            echo json_encode(['code'=>2,'font'=>'成功']);
        }
    }
    //更改购买数量  数据库
    public function changeNumberDb($goods_id,$buy_number){
        //获取用户id
        $user=session('user');
        $user_id=$user['id'];
        //where条件  商品id  用户id  购物未删除
        $where=[
            ["goods_id","=",$goods_id],
            ["user_id","=",$user_id],
            ["is_del","=",1]
        ];
        //在购物车表中改购物车里的购买数量  原先的购买数量改为最新的购买数量
        $res=Cart::where($where)->update(["buy_number"=>$buy_number]);
        return $res;
    }
    //更改购买数量  session
    public function changeNumberCookie($goods_id,$buy_number){
        //取出session
        $cartInfo=session('cartInfo');
        //判断购物车数据
        if(!empty($cartInfo)){
            //根据商品id将session的值改成新值
            $cartInfo[$goods_id]["buy_number"]=$buy_number;
            //重新存入session
            session(['cartInfo'=>$cartInfo]);
            return true;
        }
    }
    //重新获取小计
    public function getTotal(Request $request){
        //获取商品id
        $goods_id=$request->input("goods_id");
        $goods_price=ShopGoods::where("goods_id",$goods_id)->value("goods_price");
        $user_id=$this->checkLogin();
        if(!empty($user_id)){
            //获取购买数量  数据库
            $buy_number=$this->getBuyNumberDb($goods_id);
        }else{
            //获取购买数量  cookie
            $buy_number=$this->getBuyNumberCookie($goods_id);
        }
        return $goods_price*$buy_number;
    }
    //重新获取总价
    public function getMoney(Request $request){
        //接收商品id
        $goods_id=$request->input("goods_id");
        $user_id=$this->checkLogin();
        //判断用户是否登录
        if(!empty($user_id)){
            //获取总价  数据库
            $money=$this->getMoneyDb($goods_id);
        }else{
            //获取总价
            $money=$this->getMoneyCookie($goods_id);
        }
        echo $money;
    }
    //获取总价  数据库
    public function getMoneyDb($goods_id){
        //获取用户id
        $user=session('user');
        $user_id=$user['id'];
        //where条件
        $goods_id=explode(",",$goods_id);
        $where=[
            ["shop_cart.user_id","=",$user_id],
            ["is_del","=",1]
        ];
        //两表联查
        $info=Cart::leftjoin('shop_goods',"shop_cart.goods_id","=","shop_goods.goods_id")
                       ->where($where)
                       ->wherein("shop_goods.goods_id",$goods_id)
                       ->get();
        $money=0;
        foreach($info as $k=>$v){
            $money+=$v["goods_price"]*$v["buy_number"];
        }
        return $money;
    }
    //获取总价  session
    public function getMoneyCookie($goods_id){
        $cartInfo=session('cartInfo');
        if(!empty($cartInfo)){
            //将数组分割成字符串
            $goods_id=explode(",",$goods_id);
            $money=0;
            foreach($cartInfo as $k=>$v){
                if(in_array($v["goods_id"],$goods_id)){
                    //获取单价的值
                    $goods_price=ShopGoods::where("goods_id",$v["goods_id"])->value("goods_price");
                    //求总价
                    $money+=$goods_price*$v["buy_number"];
                }
            }
            return $money;  
        }
    }
    //获取购买数量  数据库
    public function getBuyNumberDb($goods_id){
        //获取用户id
        $user=session('user');
        $user_id=$user['id'];
        $where=[
            ["goods_id","=",$goods_id],
            ["user_id","=",$user_id],
            ["is_del","=",1]
        ];
        $buy_number=Cart::where($where)->value("buy_number");
        return $buy_number;
    }
    //获取购买数量  session
    public function getBuyNumberCookie($goods_id){
        //取出session
        $cartInfo=session('cartInfo');
        if(!empty($cartInfo)){
            //print_r($cartInfo);exit;
            //从session中取出当前商品的购买数量
            return $cartInfo[$goods_id]['buy_number'];
        }
    }
    //删除
    public function del(Request $request){
        //获取商品id
        $goods_id=$request->input("goods_id");
        $user_id=$this->checkLogin();
        //判断用户是否登录
        if(!empty($user_id)){
            //删除  数据库
            $res=$this->getDelDb($goods_id);
        }else{
            //删除  cookie
            $res=$this->getDelCookie($goods_id);
        }
        if($res){
            //删除成功
            echo json_encode(['code'=>1,'font'=>'删除成功']);
        }else{
            echo json_encode(['code'=>2,'font'=>'删除失败']);
        }
    }
    //删除  数据库
    public function getDelDb($goods_id){
        //获取用户id
        $user=session('user');
        $user_id=$user['id'];
        //where条件
        $where=[
            ["goods_id","=",$goods_id],
            ["user_id","=",$user_id],
            ["is_del","=",1]
        ];
        $res=Cart::where($where)->update(["is_del"=>2]);
        return $res;
    }
    //删除  session
    public function getDelCookie($goods_id){
        $cartInfo=session("cartInfo");
        if(!empty($cartInfo)){
            unset($cartInfo[$goods_id]);
        }
        session("cartInfo",$cartInfo);
        $aa=session('cartInfo');
        echo "<pre>";print_r($aa);echo "<pre>";die;
        return true;
    }
    //确认结算
    public function settlement(Request $request){
        //判断用户是否登录
        $user=session('user');
        $user_id=$user['id'];
        if(empty($user_id)){
            echo json_encode(['code'=>3,'font'=>'请先登录','url'=>"/login/login"]);die;
        }
        //接收商品id
        $goods_id=$request->input("goods_id");
        // echo $goods_id;die;
        if(empty($goods_id)){
            echo json_encode(['code'=>2,'font'=>"购物车还没有商品"]);exit;
        }
        $where=[
            ["shop_cart.user_id","=",$user_id],
            ["is_del","=",1]
        ];
        if(strpos($goods_id,',')){
            $goods_id1=explode(",",$goods_id);
            $info=Cart::leftjoin('shop_goods',"shop_cart.goods_id","=","shop_goods.goods_id")
                       ->where($where)
                       ->wherein("shop_goods.goods_id",$goods_id1)
                       ->select('shop_cart.goods_id','shop_goods.goods_price','shop_cart.user_id','shop_cart.buy_number')
                       ->get();
        }else{
            $info=Cart::leftjoin('shop_goods',"shop_cart.goods_id","=","shop_goods.goods_id")
                       ->where($where)
                       ->where("shop_goods.goods_id",$goods_id)
                       ->select('shop_cart.goods_id','shop_goods.goods_price','shop_cart.user_id','shop_cart.buy_number')
                       ->get();
        }
        // echo "<pre>";print_r($info->toArray());echo "<pre>";die;
        //开启事务--前提  表的存储引擎，必须为innodb
        DB::beginTransaction();

        //1.订单表的添加
        $order_no=time().rand(10,99).$user_id.rand(100,999);//订单号
        $order_amount=0;
        foreach($info as $k=>$v){
            $order_amount+=$v['goods_price']*$v['buy_number'];
        }
        $orderInfo=[
            'order_no'=>$order_no,
            'order_amount'=>$order_amount,
            'pay_status'=>2,
            'user_id'=>$user_id,
            'order_time'=>time(),
        ];
        $order_id=Order::insertGetId($orderInfo);//获取刚添加的订单id
        if(empty($order_id)){
            DB::rollBack();//回滚
            echo json_encode(['code'=>2,'font'=>'订单表数据添加失败']);exit;
        }
        //2.根据商品id、用户id删除购物车表的数据
        $where=[
            ['user_id','=',$user_id]
        ];
        if(strpos($goods_id,',')){
            $res2=Cart::where($where)->wherein("goods_id",$goods_id1)->update(['is_del'=>2]);
        }else{
            $res2=Cart::where($where)->where("goods_id",$goods_id)->update(['is_del'=>2]);
        }
        if(empty($res2)){
            DB::rollBack();//回滚
            echo json_encode(['code'=>2,'font'=>'删除购物车表数据失败']);exit;
        }
        //3.根据商品id对商品表的库存进行减少
        foreach($info as $k=>$v){
            $where=[
                ['goods_id','=',$v['goods_id']]
            ];
            $res3=ShopGoods::where($where)->decrement('goods_num',$v['buy_number']);
            if(empty($res3)){
                DB::rollback();//回滚
                echo json_encode(['code'=>2,'font'=>'修改商品表库存失败']);exit;
            }
        }
        DB::commit();
        //跳转支付
        echo json_encode(['code'=>1,'url'=>'/pay?order_id='.$order_id]);
    }
    //支付页面
    public function pay(){
        return view('index.cart.pay');
    }
    //确定支付
    public function payDo(Request $request){
        $order_id = $request->get('order_id');     // 支付的订单id
        $pay_type = $request->get('pay_type',1);   //支付方式
        if($pay_type==1)
        {
            $redirect_url = $this->aliPay($order_id);
        }
        return redirect($redirect_url);
    }
    
    //支付宝支付
    protected function aliPay($order_id){
        //根据订单查询到订单信息  订单号  订单金额
        $o = Order::find($order_id);
        // echo "<pre>";print_r($o->toArray());echo "<pre>";
        if(empty($o))       //订单不存在
        {
            echo "无此订单";
            die;
        }
//        echo '<pre>';print_r($o->toArray());echo '</pre>';
//        调用 支付宝支付接口

        // 1 请求参数
        $param2 = [
            'out_trade_no'      => $o->order_no,     //商户订单号
            'product_code'      => 'FAST_INSTANT_TRADE_PAY',
            'total_amount'      => $o->order_amount,    //订单总金额
            'subject'           => 'Mstore-测试订单-'.Str::random(16),
        ];

        // 2 公共参数
        $param1 = [
            'app_id'        => '2016101900723546',
            'method'        => 'alipay.trade.page.pay',
            'return_url'    => 'http://shop.1910.com/pay/alireturn',   //同步通知地址
            'charset'       => 'utf-8',
            'sign_type'     => 'RSA2',
            'timestamp'     => date('Y-m-d H:i:s'),
            'version'       => '1.0',
            'notify_url'    => 'http://shop.1910.com/pay/alinotify',   // 异步通知
            'biz_content'   => json_encode($param2),
        ];


        // 计算签名
        ksort($param1);

        $str = "";
        foreach($param1 as $k=>$v)
        {
            $str.= $k.'='.$v.'&';
        }
        $str = rtrim($str,'&');     // 拼接待签名的字符串
        $sign = $this->sign($str);
        //沙箱测试地址
        $url = 'https://openapi.alipaydev.com/gateway.do?'.$str.'&sign='.urlencode($sign);
        return $url;
    }
    //支付宝支付签名
    protected function sign($data){
        $priKey = file_get_contents(storage_path('keys/alipay_priv.key'));
        $res = openssl_get_privatekey($priKey);
        if(!$res){
            die('您使用的私钥格式错误，请检查RSA私钥配置');
        }
        openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);
        openssl_free_key($res);
        $sign = base64_encode($sign);
        return $sign;
    }
    //支付宝异步通知
    public function aliNotify(){

    }
    //支付宝同步通知
    public function aliReturn(){
        $data=[
            'msg'=>"订单： ". $_GET['out_trade_no'] . "支付成功！！!",
        ];
        return view('index.cart.success',['data'=>$data]);
    }
}
