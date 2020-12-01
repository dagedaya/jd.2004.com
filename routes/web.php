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
//查看扩展
Route::get('/info',function (){
    phpinfo();
});
/**
 * 项目开发路由
 */
Route::get('/lianxi','index\TestController@lianxi');

Route::get('/','index\IndexController@index');//前台首页
Route::get('register','index\LoginController@register');//注册视图
Route::get('login/registerdo','index\LoginController@registerdo');//执行注册
Route::get('login','index\LoginController@login');//登陆视图
Route::post('login/logindo','index\LoginController@logindo');//执行登陆
//Route::prefix('/')->middleware('login')->group(function() {
    Route::get('cart/add','index\CartController@add');//加入购物车
//});
Route::get('/', 'index\IndexController@index');//首页
Route::get('list','index\ShopController@list');//商品列表页
Route::get('detail','index\ShopController@detail');//商品详情页和缓存


Route::get('/exit','index\IndexController@exit');//退出
Route::get('login/active','index\LoginController@active');//激活用户
Route::get('/login/sendEmail','index\LoginController@sendEmail');//邮箱
Route::get('login/email','index\LoginController@email');//
Route::get('login/yesemail/{email}','index\LoginController@yesemail');//发送验证成功之后注册成功
Route::get('cart','index\CartController@cart');//购物车页面
Route::get('order/create','index\OrderController@create');//生成订单
Route::get('commit','index\OrderController@commit');//提交订单页面

//支付
Route::get('pay/paysuccess','index\AlipayController@AliPayReturn');//支付成功页面
Route::get('pay/payfail','index\AlipayController@AliPayReturn');//支付失败页面

//github登陆
Route::get('github/callback','index\LoginController@callback');
Route::get('login/callback','index\LoginController@callback');//github登陆

//收藏
Route::get('no_collect','index\ShopController@no_collect')->middleware('login');//商品收藏
Route::get('off_collect','index\ShopController@off_collect')->middleware('login');//取消收藏

//搜索
Route::get('search','index\IndexController@search');//搜索

//评论
Route::get('/comment','index\ShopController@comment');//评论
Route::get('/comment1','index\ShopController@comment1');//展示评论

//抽奖
Route::get('prize/index','index\PrizeController@index');//抽奖视图
Route::get('prize/add','index\PrizeController@add');//开始抽奖
Route::get('prize/ceshi','index\PrizeController@ceshi');//测试

    //电影购买系统
Route::get('test/movie','index\TestController@movie');//视图
Route::get('test/movieadd','index\TestController@movieadd');

//递归（斐波那契数列第n项）
Route::get('test/fab','index\TestController@fab');

//签到
Route::get('/center','index\CenterController@center')->middleware('login');//个人中心视图页面
Route::get('/btn','index\CenterController@btn');//签到

//支付宝支付处理路由
Route::get('index/alipay','index\AlipayController@Alipay');  // 发起支付请求
Route::any('cart/sync','index\AlipayController@AliPayReturn'); //服务器异步通知页面路径
Route::any('index/return','index\AlipayController@AliPayNotify');  //页面跳转同步通知页面路径

//优惠券
Route::get('/new_coupon','index\CouponController@new_coupon');//新人领取优惠劵视图页面
Route::post('add','index\CouponController@add');//领取优惠劵

//时间
Route::get('/expire','index\TestController@expire');









/**
 * 微信开发路由
 */
//微信开发者服务器接入(即支持get又支持post)
Route::match(['get','post'],'/wx','index\WxController@checkSignature');
//上传素材
Route::get('/guzzle2','index\WxController@guzzle2');
//获取access_token
Route::get('/access_token','index\WxController@access_token');
//天气(780)
Route::get('/weather1','index\WxController@weather1');
//自定义菜单
Route::get('/create_menu','index\WxController@create_menu');
//微信网页授权
Route::get('/web_auth','index\WxController@wxWebAuth');
//微信授权后跳转
Route::get('/web_redirect','index\WxController@wxWebRedirect');
//客服发送消息
Route::post('/service','index\WxController@service');


/**
 * 小程序
 */
Route::prefix('/api')->group(function (){
    Route::get('/test','weixin\ApiController@test');//首页
    Route::post('/getcode','weixin\ApiController@getcode');//登录
    Route::get('/goods','weixin\ApiController@goods');//获取商品信息
    Route::get('/goods_details','weixin\ApiController@goods_details');//根据id获取商品信息
    Route::get('/GoodsList','weixin\ApiController@GoodsList');//触底刷新
    Route::get('Cart','weixin\ApiController@Cart');//加入购物车
    Route::get('list','weixin\ApiController@list');//购物车列表
    Route::get('/collect','weixin\ApiController@collect');//加入收藏
    Route::get('/collect1','weixin\ApiController@collect1');//取消收藏
    Route::get('/decr','weixin\ApiController@decr');//减去一件商品
    Route::get('/add','weixin\ApiController@add');//加入一件商品
    Route::post('/delete','weixin\ApiController@delete');//删除所有商品
    Route::get('/del','weixin\ApiController@del');//单独删除商品
});





/**
 * 测试
 */
//测试1
Route::get('/weather','index\WxController@weather');
//测试2
Route::get('/test','index\WxController@test');
//测试3(postman)
Route::get('test2','index\WxController@test2');//get
Route::post('test3','index\WxController@test3');//post(form-data)
Route::post('test4','index\WxController@test4');//post(raw)
Route::get('test5','index\WxController@test5');//测试下载素材
//测试路由分组 test(prefix)
Route::prefix('/test')->group(function (){
    Route::get('/guzzle1','index\WxTestController@guzzle1');//使用guzzl发送get请求
    Route::get('/guzzle2','index\WxTestController@guzzle2');//上传素材
    Route::get('/weather','index\WxTestController@weather');//天气780
});
