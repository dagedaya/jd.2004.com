<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/','index\IndexController@index');//前台首页
Route::get('register','index\LoginController@register');//注册视图
Route::get('login/registerdo','index\LoginController@registerdo');//执行注册
Route::get('login','index\LoginController@login');//登陆视图
Route::get('login/logindo','index\LoginController@logindo');//执行登陆
//Route::prefix('/')->middleware('login')->group(function() {
    Route::get('cart/add','index\CartController@add');//加入购物车
//});
Route::get('/', 'index\IndexController@index');//首页
Route::get('list','index\ShopController@list');//商品列表页
Route::get('detail','index\ShopController@detail');//商品详情页和缓存


Route::get('login/exit','index\IndexController@exit');//退出
Route::get('login/active','index\LoginController@active');//激活用户
Route::get('/login/sendEmail','index\LoginController@sendEmail');//邮箱
Route::get('login/email','index\LoginController@email');//
Route::get('login/yesemail/{email}','index\LoginController@yesemail');//发送验证成功之后注册成功
Route::get('cart','index\CartController@cart');//购物车页面
Route::get('order/create','index\OrderController@create');//生成订单
Route::get('commit','index\OrderController@commit');//提交订单页面

Route::get('pay/paysuccess','index\AlipayController@AliPayReturn');//支付成功页面
Route::get('pay/payfail','index\AlipayController@AliPayReturn');//支付失败页面

Route::get('github/callback','index\LoginController@callback');
Route::get('login/callback','index\LoginController@callback');//github登陆


Route::get('no_collect','index\ShopController@no_collect');//商品收藏
Route::get('off_collect','index\ShopController@off_collect');//取消收藏

Route::get('search','index\IndexController@search');//搜索

Route::get('shop/comment','index\ShopController@comment');//评论
Route::get('shop/comment1','index\ShopController@comment1');//展示评论

Route::get('prize/index','index\PrizeController@index');//抽奖视图
Route::get('prize/add','index\PrizeController@add');//开始抽奖
Route::get('prize/ceshi','index\PrizeController@ceshi');//测试

//电影购买系统
Route::get('test/movie','index\TestController@movie');//视图
Route::get('test/movieadd','index\TestController@movieadd');

//支付宝支付处理路由
Route::get('index/alipay','index\AlipayController@Alipay');  // 发起支付请求
Route::any('cart/sync','index\AlipayController@AliPayReturn'); //服务器异步通知页面路径
Route::any('index/return','index\AlipayController@AliPayNotify');  //页面跳转同步通知页面路径



