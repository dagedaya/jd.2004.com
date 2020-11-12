<?php

namespace App\Http\Controllers\index;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\IndexModel\TypeModel;
use Illuminate\Support\Str;
use App\IndexModel\CouponModel;
class CouponController extends Controller
{
    //新人优惠劵视图页面

    public function new_coupon(){
        //优惠劵类型展示
        $type=TypeModel::where('is_use',1)->get();
        if(is_object($type)) {
            $type = $type->toArray();
        }
        $type=[
            'type'=>$type,
        ];
        return view('coupon/new_coupon',$type);
    }
    //领取优惠劵
    public function add(Request $request){
        $user_id=session()->get('user_id');
        if(empty($user_id)){
            return redirect('/login')->with('msg','请先登录');
        }
        $coupon_id=$request->fullfew;
        //根据coupon_id判断用户是否非法登录
        $res=TypeModel::where('coupon_id',$coupon_id)->first();
        if(empty($res)){
            return redirect('/')->with('msg','非法操作');
        }
        if(is_object($res)){
            $res=$res->toArray();
        }
        $data=[
            'user_id'=>$user_id,
            'coupon_num'=>Str::random(32),
            'coupon_id'=>$coupon_id,
            'coupon_time'=>strtotime(date('Y-m-d H:i:s',strtotime('+1day'))),
        ];
//        dd(date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s',strtotime('+1day')))));
        $res=CouponModel::insert($data);
        if($res){
            return redirect('/cart');
        }else{
            return redirect('coupon/new_coupon');
        }
    }
}
