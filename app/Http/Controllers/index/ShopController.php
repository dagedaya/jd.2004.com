<?php

namespace App\Http\Controllers\index;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\IndexModel\GoodsModel;
use App\IndexModel\CollectModel;
class ShopController extends Controller
{
    //商品列表页
    public function list(){
        return view('/shop/list');
    }
    //商品详情页和缓存
    public function detail(Request $request){
        $goods_id=$request->get('id');
//        $id=$request->get('id');
//        $key="h:goods:".$id;
//        //查看缓存
//        $g=Redis::hGetAll($key);
//        if($g){
//            echo "有缓存，不用查询数据库";exit;
//        }else{
//            echo "无缓存，正在查询数据库";exit;
//            //获取商品信息
//            $goods=GoodsModel::find($id);
//            if(empty($goods)){
//                echo "商品不存在";exit;
//            }
//            $goods=$goods->toArray();
//            //存入缓存
//            Redis::hMset($key,$goods);
//            echo "数据存入redis中";exit;
//        }
//        echo '<pre>';print_r($goods);echo '</pre>';
//        $data=[
//            'good'=>$goods,
//        ];
        $goods = GoodsModel::find($goods_id)->toArray();
        $user_id=session()->get('user_id');
        //根据用户id查商品有没有被收藏
        $collect=CollectModel::where('user_id',$user_id)->first();
        if($collect){
            $collect=1;
        }else{
            $collect=2;
        }
        $data = [
            'g' => $goods,
            'c' => $collect,
        ];
        return view('/shop/detail',$data);
    }
    //商品收藏
    public function no_collect(Request $request){
        $goods_id=$request->get('id');
        if(empty($goods_id)){
            echo "非法操作";die;
        }
        $user_id=session()->get('user_id');
        if(empty($user_id)){
            echo "请先登录";die;
        }
        $res=CollectModel::insert(['user_id'=>$user_id,'goods_id'=>$goods_id,'collect_time'=>time()]);
        if($res){
            echo "ok";die;
        }else{
            echo "no";die;
        }
    }
    //取消收藏
    public function off_collect(Request $request){
        $goods_id=$request->get('id');
        if(empty($goods_id)){
            echo "非法操作";die;
        }
        $user_id=session()->get('user_id');
        if(empty($user_id)){
            echo "请先登录";die;
        }
        $where=[
            ['user_id','=',$user_id],
            ['goods_id','=',$goods_id],
        ];
        $res=CollectModel::where($where)->delete();
        if($res){
            echo "ok";die;
        }else{
            echo "no";die;
        }
    }
}

