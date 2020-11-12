<?php

namespace App\Http\Controllers\index;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\IndexModel\SignModel;
use App\IndexModel\LoginModel;
class CenterController extends Controller
{
    //用户中心视图
    public function center(){
//        $user_id=session()->get('user_id');
//        if(empty($user_id)){
//            return redirect('/login')->with('msg','请先登录');
//        }
        return view('center/center');
    }
    //签到
    public function btn(){
        $user_id=session()->get('user_id');
        if(empty($user_id)){
            $data=[
                'error'=>400002,
                'msg'=>"请先登录",
            ];
            return $data;
        }
        //检查当天有没有签到
        $time=strtotime(date('Y-m-d',time()));//凌晨12点
        $sign=SignModel::where([['user_id',$user_id],['sign_time','=',$time]])->first();
        if($sign){
            $data=[
                'error'=>300008,
                'msg'=>"一天只可以签到一次",
            ];
            return $data;
        }
        $data=[
            'user_id'=>$user_id,
            'sign_time'=>$time,
        ];
        $sign=SignModel::insert($data);
        if($sign){
            $count=LoginModel::where('user_id',$user_id)->first();
            if(is_object($count)){
                $count=$count->toArray();
            }
            $data=[
                'count'=>$count['count']+100,
            ];
            LoginModel::where('user_id',$user_id)->update($data);
            $data=[
                'error'=>'20000',
                'msg'=>'签到成功+100积分'
            ];
            return $data;
        }else{
            $data=[
                'error'=>400001,
                'msg'=>'签到失败'
            ];
            return $data;
        }
    }
}
