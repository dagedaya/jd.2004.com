<?php

namespace App\Http\Controllers\index;

use App\Http\Controllers\Controller;
use App\IndexModel\CommentModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\IndexModel\GoodsModel;
use App\IndexModel\CollectModel;
use App\IndexModel\HistoryModel;
class ShopController extends Controller
{
    //商品列表页
    public function list()
    {
        return view('/shop/list');
    }

    //商品详情页和缓存
    public function detail(Request $request)
    {
        $goods_id = $request->get('id');
        $user_id=session()->get('user_id');
        //用户浏览历史记录
        if(!empty($user_id)){

        }
        //用户浏览历史记录
        if(!empty($user_id)){
            $data=[
                'goods_id'=>$goods_id,
                'user_id'=>$user_id,
                'history_time'=>time(),
            ];
            //短时间内是否刷新
            $history=HistoryModel::where([['user_id','$user_id'],['goods_id','=',$user_id]])->orderBy('history_time','desc')->select('history_time','history_id')->first();
        if(is_object($history)){
            $history=$history->toArray();
        }
        if(!empty($history)){
            if(time()-60>$history['history_time']){
                $res=HistoryModel::insert($data);
            }else{
                HistoryModel::where([['user_id','=',$user_id],['goods_id','=',$goods_id],['history_id','=',$history['history_id']]])->update(['history_time'=>time()]);
            }
        }else{
            $res=HistoryModel::insert($data);
        }
        }
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
//
//        }
//        echo '<pre>';print_r($goods);echo '</pre>';
//        $data=[
//            'good'=>$goods,
//        ];
        //浏览排行榜
        GoodsModel::where('goods_id',$goods_id)->increment('click_count');
        $goods = GoodsModel::find($goods_id);
        if(is_object($goods)){
            $goods=$goods->toArray();
        }
        $user_id = session()->get('user_id');
        //根据用户id查商品有没有被收藏
        $collect = CollectModel::where('user_id', $user_id)->first();
        if ($collect) {
            $collect = 1;
        } else {
            $collect = 2;
        }
        $data = [
            'g' => $goods,
            'c' => $collect,
        ];
        //查看评论
        $res = CommentModel::where('user_id', $user_id)->get()->toArray();
        if (is_object($res)) {
            $res->toArray();
        }
        return view('/shop/detail', $data,['res'=>$res]);
    }

    //商品收藏
    public function no_collect(Request $request)
    {
        $goods_id = $request->get('id');
        //收藏排行榜
        if (empty($goods_id)) {
            echo "非法操作";
            die;
        }
        $user_id = session()->get('user_id');
        if (empty($user_id)) {
            echo "请先登录";
            die;
        }
        $res = CollectModel::insert(['user_id' => $user_id, 'goods_id' => $goods_id, 'collect_time' => time()]);
        if ($res) {
            //收藏排行
            GoodsModel::where('goods_id',$goods_id)->increment('fav_count');
            echo "ok";
            die;
        } else {
            echo "no";
            die;
        }
    }

    //取消收藏
    public function off_collect(Request $request)
    {
        $goods_id = $request->get('id');
        if (empty($goods_id)) {
            echo "非法操作";
            die;
        }
        $user_id = session()->get('user_id');
//        if (empty($user_id)) {
//            echo "请先登录";
//            die;
//        }
        $where = [
            ['user_id', '=', $user_id],
            ['goods_id', '=', $goods_id],
        ];
        $res = CollectModel::where($where)->delete();
        if ($res) {
            echo "ok";
            die;
        } else {
            echo "no";
            die;
        }
    }

    //评论
    public function comment(Request $request)
    {
        $goods_id = $request->goods_id;
        $comment_content = $request->comment_content;
        $user_id = session()->get('user_id');
        if (empty($user_id)) {
            $data = [
                'error' => 400003,
                'msg' => "请先登录",
            ];
            return json_encode($data, true);
        }

        //入库
        $data = [
            "goods_id" => $goods_id,
            "user_id" => $user_id,
            "comment_content" => $comment_content,
            "comment_time" => time(),
        ];
        $res = CommentModel::insert($data);
        if ($res) {
            $data = [
                'error' => 500,
                'msg' => "评论成功",
            ];
            return json_encode($data, true);
        } else {
            $data = [
                'error' => 600,
                'msg' => "评论失败",
            ];
            return json_encode($data, true);
        }
    }
}

