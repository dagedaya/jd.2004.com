<?php

namespace App\Http\Controllers\Index;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\IndexModel\PrizeModel;
class PrizeController extends Controller
{
    //抽奖视图
    public function index(){
        return view('prize/index');
    }
    //开始抽奖
    public function add(Request $request){
        $user_id=session()->get('user_id');
        if(empty($user_id)){
            $data=[
                'error' => 400003,
                'msg' => "请先登录",
            ];
            return json_encode($data,true);
        }
        //检查当天是否有抽奖纪录
        $time=strtotime(date('Y-m-d',time()));
        $hours=date('Y-m-d H:i:s',$time);
        $prize_record=PrizeModel::where('user_id',$user_id)->where('add_time','>=',$hours)->first();
        if($prize_record){
            $data=[
                'error'=>300008,
                'msg'=>"今天已经抽奖了，明天再来吧",
            ];
            return $data;
        }
        $rand=mt_rand(1,1000);
        $prize='未中奖';
        if($rand<=1&&$rand>=10){
            $prize="一等奖";
        }else if($rand<=11&&$rand>=40){
            $prize='二等奖';
        }else if($rand<=41&&$rand>=60){
            $prize='三等奖';
        }
        //纪录抽奖信息
        $prize_data=[
            'user_id'=>$user_id,
            'level'=>$prize,
            'add_time'=>time(),
        ];
        $res=PrizeModel::insert($prize_data);
        if($res){
            $data=[
                'error'=>0,
                'msg'=>'ok',
                'data'=>[
                    'prize'=>$prize,
                ],
            ];
        }else{
            $data=[
                'error'=>500008,
                'msg'=>"数据发生错误,请重试",
            ];
        }
        return $data;
    }
}

