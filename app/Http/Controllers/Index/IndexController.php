<?php

namespace App\Http\Controllers\Index;
 
use App\Http\Controllers\Controller;
use App\Model\ShopGoods;
use App\Model\ShopModel;
use Illuminate\Http\Request;
use App\Model\ShopGoods as model_shopgoods;
use App\Model\Cate as model_cate;
use App\Model\Video;

class IndexController extends Controller
{
    //首页
    public function index(){

         //轮播图循环
         $imagemodel = ShopGoods::orderBy('goods_id')->limit(3)->get()->toArray();
          


        $dir=dirname(app_path()).'/resources/views/index';
        if(file_exists($dir.'/index.blade.php') && time()<filemtime($dir.'/index.blade.php')+20){
            $content1=file_get_contents($dir.'/index.blade.php');
            echo $content1;die;
        }
        //分类查询
        $data=model_cate::get()->toArray();
        //商品查询
        //新品
        $res= model_shopgoods::where('is_new',1)->orderBy('goods_id','DESC')->limit(4)->get()->toArray();
        //热卖
        $hot= model_shopgoods::where('is_hot',2)->limit(4)->get()->toArray();
        
        ob_start();
        $content=view("index.index.index",['data'=>$data,'res'=>$res,'img'=>$imagemodel,'hot'=>$hot]);
        file_put_contents($dir.'/index.blade.php',$content);
        return $content;
    }

    public function index1(){
        $imgmodel = ShopModel::orderBy('DESC')->limit(3)->get()->toArray();
        return view('index.index.index',['img'=>$imgmodel]);
    }

    //列表
    public function link(){
        //分类查询
        $data=model_cate::get()->toArray();
        //商品查询
        $res= model_shopgoods::get()->toArray();
        return view('index.index.link',['data'=>$data,'res'=>$res]);
    }
    //商品详情
    public function details($id){
        $dir=dirname(app_path()).'/resources/views/index';
        if(file_exists($dir.'/details.blade.php') && time()<filemtime($dir.'/details.blade.php')+20){
            $content1=file_get_contents($dir.'/details.blade.php');
            echo $content1;die;
        }
        $data= model_shopgoods::where('goods_id',$id)->find($id)->toArray();
        $aaa=Video::where('goods_id',$id)->get()->toArray();
        ob_start();
        if(empty($aaa)){
            $content=view('index.index.details',['data'=>$data]);
            file_put_contents($dir.'/details.blade.php',$content);
            return $content;
        }else{
            $data['goods_m3u8']=$aaa[0]['goods_m3u8'];
            $content=view('index.index.details',['data'=>$data]);
            file_put_contents($dir.'/details.blade.php',$content);
            return $content;
        }
    }
    public function wishlist(){
        $pwa=model_shopgoods::where('is_new','1')->orderBy('goods_id','DESC')->limit(4)->get()->toArray();//最新
        $get=model_shopgoods::where( 'is_new', '2' )->orderBy( 'goods_id', 'DESC' )->limit(4)->get()->toArray();//热卖
        return view('index.index.wishlist',['pwa'=>$pwa,'get'=>$get]);
    }

    public function wish(){
        return view('index.index.wish');
    }
}
