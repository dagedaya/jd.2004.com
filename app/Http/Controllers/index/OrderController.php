<?php

namespace App\Http\Controllers\index;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\IndexModel\CartModel;
use App\IndexModel\OrderModel;
use App\IndexModel\GoodsModel;
use App\IndexModel\Orders_GoodsModle;
class OrderController extends Controller
{
    //提交订单页面
    public function commit(){
        return view('order/commit');
    }
    /**
     *生成订单
     */
    public function create(Request $request){
//        echo __METHOD__;
        $user_id=$request->session()->get('user_id');
        if(empty($user_id)){
            return redirect('login/login')->with('msg','请先登录');
        }
        //TODO 获取购物车中的商品（根据当前的用户id）
        $cart=CartModel::where('user_id',$user_id)->first();
        //TODO 生成订单号 计算订单总价 纪录订单信息（订单表orders）
        //生成订单号
        //选择一个随机的方案
        mt_srand((double) microtime() * 1000000);
        $order_sn=date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        //计算订单总价

        //纪录订单信息（订单表orders）
        $data=[
            'order_sn'=>$order_sn,//生成的订单号
            'user_id'=>$user_id,//用户id
            'order_status'=>0,//订单状态
            'pay_status'=>0,//支付状态
        ];
        //新增一条纪录并返回id
        $order_id=OrderModel::insertGetId($data);
        //TODO 纪录订单商品（订单商品表orders_goods）
        //根据用户id查询购物车表商品id
        $goods_id=CartModel::where('user_id',$user_id)->get()->toArray();//打印是二维数组
        $money=0;
        foreach($goods_id as $k=>$v) {
            $info = GoodsModel::where('goods_id', $v['goods_id'])->first(['shop_price']);
            $money = $money + $info['shop_price'];
        }
        foreach($goods_id as $k=>$v){
            $goods_id=$v['goods_id'];
            //根据是商品id查询查询商品信息
            $res=GoodsModel::where('goods_id',$goods_id)->first()->toArray();
            //根据在订单表中查到的订单id添加订单商品表
            $order_goods=[
                'order_id'=>$order_id,
                'goods_id'=>$res['goods_id'],
                'goods_name'=>$res['goods_name'],
                'goods_sn'=>$res['goods_sn'],
                'goods_number'=>$res['goods_number'],
                'goods_price'=>$res['shop_price'],
            ];
            Orders_GoodsModle::insert($order_goods);
            return redirect('order/commit');
        }
        //TODO 清空购物车

        //TODO 跳转到支付页面

    }
}
