<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
*/

// Route::get('/', function () {
//     return view('welcome');
// });
Route::any("/","Index\IndexController@index");//前台首页
Route::any("/index1","Index\IndexController@index1");//轮播图
Route::any("/link","Index\IndexController@link");//商品列表
Route::any("/wishlist","Index\IndexController@wishlist");//我喜欢
Route::any("/wish","Index\IndexController@wish");//我喜欢
Route::any("/details/{id}","Index\IndexController@details");//商品详情

Route::post("/addCart","Index\CartController@addCart");//加入购物车
Route::get("/cart","Index\CartController@cartList");//购物车列表
Route::post("/changeNumber","Index\CartController@changeNumber");//更改购买数据
Route::post("/getTotal","Index\CartController@getTotal");//更改购买数据
Route::post("/getMoney","Index\CartController@getMoney");//更改购买数据
Route::post("/del","Index\CartController@del");//删除

Route::post("/settlement","Index\CartController@settlement");//结算
Route::get('/pay','Index\CartController@pay');//支付页面
Route::post('/payDo','Index\CartController@payDo');//确定支付
Route::get('/pay/alireturn','Index\CartController@aliReturn');//支付宝同步通知
Route::get('/pay/alinotify','Index\CartController@aliNotify');//支付宝异步通知

Route::prefix('login')->group(function () {
    Route::get("/reg","Index\LonginController@reg");//前台注册
    Route::post("/send","Index\LonginController@send");//验证码
    Route::post("/regdo","Index\LonginController@regdo");//执行注册
    Route::get("/login","Index\LonginController@login");//前台登录
    Route::post("/logindo","Index\LonginController@logindo");//执行登录
    Route::get("/github","Index\LonginController@github");//github登录
    Route::get('/githubDo','Index\LonginController@githubDo');//github授权回跳地址
});

Route::get('/crontab','VideoController@crontab');//定时转码

Route::get('/chat','Chat\IndexController@index');           //聊天室
