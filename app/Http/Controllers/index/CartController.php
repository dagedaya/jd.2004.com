<?php

namespace App\Http\Controllers\index;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\IndexModel\CartModel;
use App\IndexModel\GoodsModel;

class CartController extends Controller
{
    //购物车页面
    public function cart(){
        $user_id=session()->get('user_id');
        if(empty($user_id)){
            return redirect('login/login')->with('msg','请先登录');
        }
        //取出购物车商品信息
        $list=CartModel::where('user_id',$user_id)->get();
        $goods = [];
        foreach($list as $k=>$v){
            $goods[]=GoodsModel::find($v['goods_id'])->toArray();
        }
        $data=[
            'goods'=>$goods,
        ];
        return view('order/cart',$data);
    }
    //加入购物车
    public function add(Request $request){
        $user_id=$request->session()->get('user_id');
        if(empty($user_id))
        {
            $data = [
                'erron' => 400001,
                'msg'   => '请先登录'
            ];
            echo json_encode($data);
            exit;
        }
        $goods_id=$request->get('id');
        $goods_num=$request->get('num',1);
        //购物车保存商品信息
        $cart_info=[
            'goods_id'=>$goods_id,
            'goods_num'=>$goods_num,
            'add_time'=>time(),
            'user_id'=>$user_id,
        ];
        //入库(新增记录并返回id)
        $res=CartModel::insertGetId($cart_info);
        if($res>0){
            $data=[
                'erron'=>1,
                'msg'=>'加入购物车成功',
            ];
            echo json_encode($data);
        }else{
            $data=[
                'erron'=>500001,
                'msg'=>'加入购物车失败',
            ];
            echo json_encode($data);
        }
    }
}
