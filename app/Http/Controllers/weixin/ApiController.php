<?php

namespace App\Http\Controllers\weixin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Model\XcxUserModel;

class ApiController extends Controller
{
    public function __construct()
    {
        app('debugbar')->disable();//关闭调试
    }
    //接口获取数据（并渲染视图）
    public function test(){
//        echo '<pre>';print_r($_POST);echo '</pre>';
//        echo '<pre>';print_r($_GET);echo '</pre>';
        $goods_info=[
            'goods_id'=>'217',
            'goods_name'=>'iphone',
            'shop_price'=>12.34
        ];
        echo json_encode($goods_info,JSON_UNESCAPED_UNICODE);
    }
    /**
     * 小程序登录
     * @param Request $request
     */
    //获取code换取session_key和openid
    public function getcode(Request $request){
      $code=$request->get('code');
      $url="https://api.weixin.qq.com/sns/jscode2session?appid=".env('WX_XCX_APPID')."&secret=".env('WX_XCX_APPSECRET')."&js_code=".$code."&grant_type=authorization_code";
      $data=json_decode(file_get_contents($url),true);
//      echo '<pre>';print_r($data);echo '</pre>';
      //自定义登录状态
        if(isset($data['errcode'])){  //有错误
            $response=[
                'error'=>500001,
                'msg'=>'登录失败',
            ];
        }else{
            XcxUserModel::insert(['openid'=>$data['openid']]);
            $token=sha1($data['openid'].$data['session_key'].mt_rand(0,999999));
            //保持token
            $redis_key='xcx_token'.$token;
            Redis::set($redis_key,time());
            //设置2小时过期时间
            Redis::expire($redis_key,7200);
            $response=[
                'error'=>0,
                'msg'=>'ok',
                'data'=>[
                    'token'=>$token,
                ]
            ];
        }
        return $response;
    }
}
