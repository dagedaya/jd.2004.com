<?php

namespace App\Http\Controllers\weixin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Model\XcxUserModel;
use App\IndexModel\GoodsModel;

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
            //保存token
            $redis_login_hash="h:xcx:login:".$token;
            $loginInfo=[
                'uid'=>'1234',
                'user_name'=>'李明',
                'login_time'=>time(),
                'login_ip'=>$request->getClientIp(),
                'token'=>$token
            ];
            //保存登录的信息
            Redis::hMset($redis_login_hash,$loginInfo);
            //设置2小时过期时间
            Redis::expire($redis_login_hash,7200);
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
    /**
     * 获取商品
     */
//    public function goods(){
//        $goods=GoodsModel::inRandomOrder()->take(10)->get()->toArray();
//        return json_encode($goods,256);
//    }
//    public function goods(){
//        $goods=GoodsModel::select()->limit(10)->get()->toArray();
//        return $goods;
//    }
    /**
     * 根据id获取商品信息
     */
    public function goods_details(Request $request){
        $goods_id=$request->get('id');
        if(!empty($goods_id)){
            $detail=GoodsModel::where('goods_id',$goods_id)->first()->toArray();
            return $detail;
        }else{
            $token=$request->get('access_token');
//        //验证token是否有效
            $token_key='h:xcx:login:'.$token;
            echo "key:".$token_key;
            echo "<hr>";
//        //检查key是否存在
            $status=Redis::exists($token_key);
            if($status==0) {
                $response = [
                    'error' => 400003,
                    'msg' => '授权失败',
                ];
                return $response;
            }
        }
    }
    /**
     * 触底刷新数据
     */
    public function GoodsList(Request $request){
//        $page=$request->get('page');
//        $size=$request->get('size');
        $page_size=$request->get('ps');//是get里面默认的参数，laravel里面的分页自动检测
        $goods_list=GoodsModel::select('goods_id','goods_name','goods_img','shop_price')->paginate($page_size);
        $response=[
            'error'=>0,
            'msg'=>'ok',
            'data'=>[
                'list'=>$goods_list->items()
            ]
        ];
        return $response;
    }
}
