<?php

namespace App\Http\Controllers\Index;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\IndexUser;
use Illuminate\Support\Facades\Redis;
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use App\Model\Github;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cookie;

class LonginController extends Controller
{
    //注册
    public function reg(){
        return view("index.login.reg");
    }
    //发送验证码
    public function send(Request $request){
        $tel=$request->input('tel');
        $reg='/^1[34578]\d{9}$/';
        if(preg_match($reg,$tel)<1){
            $font="手机号码格式不正确!";
            $arr=['font'=>$font,'code'=>2];
            echo json_encode($arr);
        }
        if(empty($tel)){
            $font="请输入手机号!";
            $arr=['font'=>$font,'code'=>2];
            echo json_encode($arr);
        }else{
            $res=IndexUser::where('tel',$tel)->get();
            if($res==NULL){
                $font="改手机号已注册!";
                $arr=['font'=>$font,'code'=>2];
                echo json_encode($arr);
            }else{
                $code=rand(100000,999999);
                //发短信
                $res=$this->sendSms($tel,$code);
                if(empty($res)){ 
                    $font="发送失败";
                    $arr=['font'=>$font,'code'=>2];
                    echo json_encode($arr);
                }else{
                    $font="发送成功";
                    //连接redis
                    $redis=new redis();
                    $redis::set('code',$code,300);
                    $arr=['font'=>$font,'code'=>1];
                    echo json_encode($arr);
                }
            }
        }
    }
    public function sendSms($tel,$code){
        AlibabaCloud::accessKeyClient('LTAI4GFh7Ubv4iDvrH6LJf2Z','G7NifJl1u0Nj6jgEa4CWFmvqcn5Sq9')
                    ->regionId('cn-hangzhou')
                    ->asDefaultClient();
        try {
            $result = AlibabaCloud::rpc()
                        ->product('Dysmsapi')
                        ->version('2017-05-25')
                        ->action('SendSms')
                        ->method('POST')
                        ->host('dysmsapi.aliyuncs.com')
                        ->options([
                                'query' => [
                                'RegionId' => "cn-hangzhou",
                                'PhoneNumbers' => "$tel",
                                'SignName' => "小白农场",
                                'TemplateCode' => "SMS_192715609",
                                'TemplateParam' => "{\"code\":\"${code}\"}",
                                ],
                        ])
                        ->request();
            return $result;
        } catch (ClientException $e) {
            echo $e->getErrorMessage() . PHP_EOL;
        } catch (ServerException $e) {
            echo $e->getErrorMessage() . PHP_EOL;
        }
    }
    //执行注册
    public function regdo(Request $request){
        $post=request()->except('_token');
        $reg='/^1[34578]\d{9}$/';
        $pwd_reg="/^[0-9A-Za-z]{6,16}$/";
        $success=[];
        //连接redis
        $redis=new redis();
        $code=$redis::get('code');
        if($code!==$post['code']){
            $success['code']='2';
            $success['url']='';
            $success['font']='验证码无效';
            echo json_encode($success,JSON_UNESCAPED_UNICODE);die;
        }
        if(preg_match($reg,$post['tel'])<1){
            $success['code']='2';
            $success['url']='';
            $success['font']='手机号有误';
            echo json_encode($success,JSON_UNESCAPED_UNICODE);die;
        }
        $user=IndexUser::where('name',$post['name'])->first();
        if (!empty($user)){
            $success['code']='2';
            $success['url']='';
            $success['font']='名称已存在请重新输入';
            echo json_encode($success,JSON_UNESCAPED_UNICODE);die;
        }
        $email=IndexUser::where('email',$post['email'])->first();
        if (!empty($email)){
            $success['code']='2';
            $success['url']='';
            $success['font']='邮箱已存在请重新输入';
            echo json_encode($success,JSON_UNESCAPED_UNICODE);die;
        }
        $tel=IndexUser::where('tel',$post['tel'])->first();
        if (!empty($tel)){
            $success['code']='2';
            $success['url']='';
            $success['font']='手机号已注册请重新输入';
            echo json_encode($success,JSON_UNESCAPED_UNICODE);die;
        }
        if(!preg_match($pwd_reg,$post['password'])){
            $success['code']='2';
            $success['url']='';
            $success['font']='密码数字字母下划线6-16位';
            echo json_encode($success,JSON_UNESCAPED_UNICODE);die;
        }
        if ($post['password']!=$post['passwords']){
            $success['code']='2';
            $success['url']='';
            $success['font']='两次密码不一致请重新输入';
            echo json_encode($success,JSON_UNESCAPED_UNICODE);die;
        }else{
            $password=password_hash($post['password'],PASSWORD_BCRYPT);
            $data['name']=$post['name'];
            $data['email']=$post['email'];
            $data['password']=$password;
            $data['tel']=$post['tel'];
            $data['addtime']=time();
            $res=IndexUser::create($data);
            if ($res){
                $success['code']='1';
                $success['url']='/login/login';
                $success['font']='注册成功';
                echo json_encode($success,JSON_UNESCAPED_UNICODE);
            }
        }

    }
    //登录
    public function login(){
        return view("index.login.login");
    }
    //执行登录
    public function logindo(Request $request){
        $name=$request->input('name');
        $password=$request->input('password');
        $user=IndexUser::where('name',$name)->first();
        if (empty($user)){
            header('Refresh:2,url=/login/login');
            echo "请输入用户名或密码";exit;
        }
        if(password_verify($password,$user['password'])){
            echo "登录成功";
            header('Refresh:2,url=/');
            session(['user'=>$user]);
        }else{
            echo "密码错误,请重新登录";
            header('Refresh:2,url=/login/login');
        }
    }
    protected $token;
    //github登录
    public function github(){
        $url = 'https://github.com/login/oauth/authorize?client_id='.env('OAUTH_GITHUB_ID').'&redirect_uri='.env('APP_URL').'/login/githubDo';
        return redirect($url);
    }
    public function githubDo(){
        // 接收code
        $code = $_GET['code'];
        //换取access_token
        $token = $this->getToken($code);
        //获取用户信息
        $git_user = $this->getGithubUserInfo($token);
        //判断用户是否已存在，不存在则入库新用户
        $u = Github::where(['guid'=>$git_user['id']])->first();
        if($u)//存在
        {
            // TODO登录逻辑
            $this->webLogin($u->uid);

        }else{ //不存在
            //在用户主表中创建新用户获取uid
            $new_user = [
                'name' => Str::random(10),//生成随机用户名，用户有一次修改机会
            ];
            $uid = IndexUser::insertGetId($new_user);
            // 在 github 用户表中记录新用户
            $info = [
                'uid'                   =>  $uid,       //作为本站新用户
                'guid'                  =>  $git_user['id'],         //github用户id
                'avatar'                =>  $git_user['avatar_url'],
                'github_url'            =>  $git_user['html_url'],
                'github_username'       =>  $git_user['name'],
                'github_email'          =>  $git_user['email'],
                'add_time'              =>  time()
            ];
            $guid = Github::insertGetId($info);
            // TODO 登录逻辑
            $this->webLogin($uid);
        }

        //将 token 返回给客户端
        Cookie::queue('token',$this->token,120,'/');//120分钟
        return redirect('/');

    }
    protected function getToken($code){
        $url = 'https://github.com/login/oauth/access_token';

        //post 接口  Guzzle or  curl
        $client = new Client();
        $response = $client->request('POST',$url,[
            'form_params'   => [
                'client_id'         => env('OAUTH_GITHUB_ID'),
                'client_secret'     => env('GITHUB_CLIENT_SECRET'),
                'code'              => $code
            ]
        ]);
        parse_str($response->getBody(),$str); // 返回字符串 access_token=59a8a45407f1c01126f98b5db256f078e54f6d18&scope=&token_type=bearer
        return $str['access_token'];
    }
    protected function getGithubUserInfo($token){
        $url = 'https://api.github.com/user';
        //GET 请求接口
        $client = new Client();
        $response = $client->request('GET',$url,[
            'headers'   => [
                'Authorization' => "token $token"
            ]
        ]);
        return json_decode($response->getBody(),true);
    }
    protected function webLogin($uid){
        $token = IndexUser::generateToken($uid);
        //服务器保存 token
        $token_key = 'h:login_info:'.$token;
        $login_info = [
            'token'         => $token,                      // 用户token
            'uid'           => $uid,                        // 用户主表 uid
            'login_time'    => date('Y-m-d H:i:s'),    //登录时间
            'login_ip'      => $_SERVER['REMOTE_ADDR'],     //客户端登录IP
        ];
        Redis::hMset($token_key,$login_info);
        Redis::expire($token_key,7200);     // 登录有效期 2 个小时

        $this->token = $token;
        //将 uid 与 token写入 seesion    （session使用Redis存储）
        $user=[
            'id'=>$uid
        ];
        session(['user'=>$user]);

    }

}
