<?php

namespace App\Http\Controllers\weixin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Model\XcxUserModel;
use App\IndexModel\GoodsModel;
use App\Model\XcxCartModel;
use App\Model\CollectModel;

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
            $user_id = $res['user_id'];
            if($res){
                //TODO用户信息已存在
                $user_id1=$res['user_id'];
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
                $user_id=XcxUserModel::insertGetId($u_info);
            }
            //sha1加密相当于md5
            $token=sha1($data['openid'].$data['session_key'].mt_rand(0,999999));
            //保存token
            $redis_login_hash="h:xcx:login:".$token;
            $loginInfo=[
                'uid'=>$user_id,
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
            // 默认为该用户 未收藏
            $iscollect = false;
            // 查询该商品有没有被用户收藏
            $token=$request->get('access_token');
            if(!empty($token)){
               $user_id=$this->getuserid($token);
                $collectInfo=CollectModel::where('user_id',$user_id)->select('goods_id')->get();
                if(is_object($collectInfo)){
                    $collectInfo = $collectInfo->toArray();
                }
                foreach ($collectInfo as $k=>$v){
                    if($v['goods_id'] == $goods_id){
                        // 如果当前访问的商品id === 收藏表中的id 则赋值为 收藏为 true
                        $iscollect = true;
                    }
                }
            }
            $result = [
                'error' =>  0,
                'msg'   =>  'ok',
                'data'  =>  [
                    'list'  =>  $detail,
                    'iscollect' => $iscollect,
                ]
            ];
            return $result;
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
        //商品价格
        $price=GoodsModel::find($goods_id)->shop_price;
        //接收token
        $token=$request->get('token');
        $user_id = $this->getuserid($token);
        //查询有没有这个商品
        $shop=XcxCartModel::where(['goods_id'=>$goods_id,'user_id'=>$user_id])->first();
        if($shop){
            XcxCartModel::where('goods_id',$goods_id)->increment('goods_num');//自增
        }else{
            $cartInfo=[
                'goods_id'=>$goods_id,
                'add_time'=>time(),
                'user_id'=>$user_id,
                'shop_price'=>$price,
                'goods_num'=>1,
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
                    'msg'=>"增加了一个亲",
                ];
            }
            return $response;
        }
    }
    /**
     * 购物车列表页(展示)
     */
    public function list(Request $request){
        //获取token,根据token找到openid,根据openid找到user_id
        $token=$request->get('token');
        $user_id = $this->getuserid($token);
        //查询购物车列表(根据user_id查找购物车列表)
        $list=XcxCartModel::where('user_id',$user_id)->get()->toArray();
        //查询购物车表总数居
        $count=XcxCartModel::count();
        $data=[];
        foreach ($list as $k=>$v){
            $goods_id=$v['goods_id'];
            $goodsInfo=GoodsModel::select('p_goods.goods_id','goods_name','p_goods.shop_price','goods_img',"goods_num")
                ->leftjoin("p_xcx_cart","p_goods.goods_id","=","p_xcx_cart.goods_id")
                ->find($goods_id);
            if(is_object($goodsInfo)){
                $goodsInfo=$goodsInfo->toArray();
            }
            $data[] = $goodsInfo;
        }
        $result=[
            'error'=>0,
            'msg'=>'查询购物车列表成功',
            'data'=>[
                'list'=>$data,
                'count'=>$count,
            ],
        ];
        return $result;
    }
    /**
     * 加入收藏
     */
    public function collect(Request $request){
        //接收商品id和token
        $goods_id=$request->get('id');
        $token=$request->get('token');
        $user_id = $this->getuserid($token);
        //加入收藏（存入数据库）
        $collect=[
            'goods_id'=>$goods_id,
            'user_id'=>$user_id,
            'add_time'=>time(),
        ];
        $collectInfo=CollectModel::insert($collect);
        if($collectInfo){
            $response=[
                'error'=>0,
                'msg'=>'ok',
            ];
        }else{
            $response=[
                'error'=>500001,
                'msg'=>'收藏失败',
            ];
        }
        return $response;
        //加入收藏（redis的有序集合）
//        $redis_key='goods_id:collect:'.$user_id;//用户收藏的商品有序集合
//        Redis::Zadd($redis_key,time(),$goods_id);//将商品id加入有序集合，并给排序值
//        $response=[
//            'error'=>0,
//            'msg'=>'ok',
//        ];
//        return $response;
    }
    /**
     * 取消收藏
     */
    public function collect1(Request $request){
        //接收商品id和token
        $goods_id=$request->get('id');
        $token=$request->get('token');
        $user_id = $this->getuserid($token);
        $collectInfo=CollectModel::where(['goods_id'=>$goods_id,'user_id'=>$user_id])->delete();
        if($collectInfo){
            $response=[
                'error'=>0,
                'msg'=>'no',
            ];
        }else{
            $response=[
                'error'=>500001,
                'msg'=>'取消收藏失败',
            ];
        }
        return $response;
    }
    /**
     * 购物车减去一件商品
     * @param $token
     * @return mixed
     */
    public function decr(Request $request){
        $goods_id=$request->get('goods_id');
        //查询购物车表
        $cart=XcxCartModel::where('goods_id',$goods_id)->value('goods_num');
        if($cart>1){
            $res=[
                'goods_num'=>$cart-1,//数量减一
            ];
            $decr=XcxCartModel::where('goods_id',$goods_id)->update($res);
        }else{
            $res=[
                'goods_num'=>1,//数量减一
            ];
            $decr=XcxCartModel::where('goods_id',$goods_id)->update($res);
        }
        $response=[
            'error'=>0,
            'msg'=>'ok',
            'data'=>[
                'list'=>$decr,
            ],
        ];
        return $response;
    }
    /**
     * 购物车添加一条商品
     * @param $token
     * @return mixed
     */
    public function add(Request $request){
        $goods_id=$request->get('goods_id');
        //查询购物车表
        $cart=XcxCartModel::where('goods_id',$goods_id)->first()->toArray();
        if($cart){
            $res=[
                'goods_num'=>$cart['goods_num']+1,//数量加一
            ];
            $add=XcxCartModel::where('goods_id',$goods_id)->update($res);
        }
        $response=[
            'error'=>0,
            'msg'=>'ok',
            'data'=>[
                'list'=>$add,
            ],
        ];
        return $response;
    }
    /**
     * 购物车删除所有商品
     * @param $token
     * @return mixed
     */
    public function delete(Request $request){
        $goods_id=$request->post('goods_id');
        $goods_arr =  explode(',',$goods_id);
        $token=$request->get('token');
        $user_id=$this->getuserid($token);
        $res = XcxCartModel::where(['user_id'=>$user_id])->whereIn('goods_id',$goods_arr)->delete();
        if($res)        //删除成功
        {
            $response = [
                'errno' => 0,
                'msg'   => 'ok'
            ];
        }else{
            $response = [
                'errno' => 500002,
                'msg'   => '内部错误'
            ];
        }
        return $response;
//        $token=$request->get('token');
//        $user_id=$this->getuserid($token);
//        $shop=XcxCartModel::where('user_id',$user_id)->delete();
//        $response=[
//            'error'=>0,
//            'msg'=>'ok',
//            'data'=>$shop,
//        ];
//        return $response;
    }
    /**
     * 购物车单独删除
     * @param $token
     * @return mixed
     */
    public function del(Request $request){
        $goods_id=$request->get('goods_id');
        $del=XcxCartModel::where('goods_id',$goods_id)->delete();
        $response=[
            'error'=>0,
            'msg'=>'ok',
            'data'=>$del
        ];
        return $response;
    }
    //获取user_id（私有--》直接调用）
    private function getuserid($token){
        $key="h:xcx:login:".$token;
        //取出openid
        $token1=Redis::hgetall($key);
        $user_id = $token1['uid'];
        return $user_id;
    }
}
