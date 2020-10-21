<?php

namespace App\Http\Controllers\index;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\IndexModel\GoodsModel;

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

        $data = [
            'g' => $goods,
        ];
        return view('/shop/detail',$data);
    }
}

