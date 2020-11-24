<?php

namespace App\Http\Controllers\weixin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Model\XcxUserModel;
use App\IndexModel\GoodsModel;
use App\Model\XcxCartModel;

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
    //获取用户信息
    $userInfo=json_decode(file_get_contents('php://input'),true);
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
            $openid=$data['openid'];
            $res=XcxUserModel::where('openid',$openid)->first();
            if($res){
                //TODO用户信息已存在
                $user_id=$res['user_id'];
//                return $user_id;
            }else{
                //新用户入库
                $u_info=[
                    'openid'=>$openid,
                    'nickname'=>$userInfo['u']['nickName'],
                    'sex'=>$userInfo['u']['gender'],
                    'language'=>$userInfo['u']['language'],
                    'city'=>$userInfo['u']['city'],
                    'province'=>$userInfo['u']['province'],
                    'country'=>$userInfo['u']['country'],
                    'headimgurl'=>$userInfo['u']['avatarUrl'],
                    'add_time'=>time(),
                ];
                $res=XcxUserModel::insertGetId($u_info);
            }
            $token=sha1($data['openid'].$data['session_key'].mt_rand(0,999999));
            //保存token
            $redis_login_hash="h:xcx:login:".$token;
            $loginInfo=[
                'uid'=>'1234',
                'user_name'=>'李明',
                'login_time'=>time(),
                'login_ip'=>$request->getClientIp(),
                'token'=>$token,
                'openid'=>$openid
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
    /**
     * 加入购物车
     */
    public function cart(Request $request){
        $goods_id=$request->get('goods_id');
        //接收token
        $token=$request->get('token');
        $key="h:xcx:login:".$token;
        //取出openid
        $token=Redis::hgetall($key);
        $user_id=XcxUserModel::where('openid',$token['openid'])->select('user_id')->first();
        $cartInfo=[
            'goods_id'=>$goods_id,
            'add_time'=>time(),
            'user_id'=>$user_id->user_id,
        ];
        $res=XcxCartModel::insert($cartInfo);
        if($res){
            $response=[
                'error'=>0,
                'msg'=>"加入购物车成功",
            ];
        }else{
            $response=[
                'error'=>500001,
                'msg'=>"加入失败",
            ];
        }
        return $response;
    }
    /**
     * 购物车列表页
     */
    public function list(Request $request){
        $str = "[{\"merchantInfo\":{\"merchantId\":\"111\",\"name\":\"这是我家的小小小店\",\"icon\":\"/assets/images/cart_none_a.png\",\"hasSelected\":false,\"isActivity\":true},\"goodsList\":[{\"merchantId\":\"111\",\"quantity\":4,\"quantityUpdatable\":false,\"hasSelected\":false,\"id\":\"217\",\"title\":\"电脑\",\"price\":50000,\"goods_img\":\"https://ss0.bdstatic.com/70cFvHSh_Q1YnxGkpoWK1HF6hhy/it/u=1995828843,2702670661&fm=26&gp=0.jpg\"}]},{\"merchantInfo\":{\"merchantId\":\"112\",\"name\":\"这是我家的小小小店\",\"icon\":\"/assets/images/cart_none_a.png\",\"hasSelected\":false,\"isActivity\":true},\"goodsList\":[{\"merchantId\":\"111\",\"quantity\":4,\"quantityUpdatable\":false,\"hasSelected\":false,\"id\":\"218\",\"title\":\"电脑\",\"price\":50000,\"goods_img\":\"https://ss0.bdstatic.com/70cFvHSh_Q1YnxGkpoWK1HF6hhy/it/u=1995828843,2702670661&fm=26&gp=0.jpg\"}]}]";
//        dd(json_decode($str,true));
        //获取token,根据token找到openid,根据openid找到user_i
        $token=$request->get('token');
        $key="h:xcx:login:".$token;
        //取出openid
        $token1=Redis::hgetall($key);
        $openid=$token1['openid'];
        $user_id=XcxUserModel::where('openid',$openid)->select('user_id')->first()->toArray();
        $user_id = $user_id['user_id'];
        //查询购物车列表(根据user_id查找购物车列表)
        $list=XcxCartModel::where('user_id',$user_id)->get()->toArray();
        $data=[];
        foreach ($list as $k=>$v){
            $goods_id=$v['goods_id'];
            $goodsInfo=GoodsModel::select('goods_name','shop_price','goods_img')->find($goods_id);
            if(is_object($goodsInfo)){
                $goodsInfo=$goodsInfo->toArray();
            }
            $arr=[
                'merchantId'=>"111",
              'quantity'=>4,
              'quantityUpdatable'=>false,
              'hasSelected'=>false,
                'id'=>"$goods_id",
                'title'=>$goodsInfo['goods_name'],
                'price'=>$goodsInfo['shop_price'],
                'goods_img'=>$goodsInfo['goods_img']
            ];
            $data[] = [
                'merchantInfo'=>[
                    'merchantId'=>"111",
                    'name'=>"这是我家的小小小店",
                    'icon'=>'/assets/images/cart_none_a.png',
                    'hasSelected'=>false,
                    'isActivity'=>true
                ],
                'goodsList'=>[$arr]
            ];
        }

//        dd($data);
        $result=[
            'error'=>0,
            'msg'=>'查询购物车列表成功',
            'data'=>[
                'list'=>$data,
            ],
        ];
        return $result;

    }
}
