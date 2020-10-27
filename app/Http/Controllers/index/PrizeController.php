<?php

namespace App\Http\Controllers\Index;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
                'error' => 400,
                'msg' => "请先登录",
            ];
            return json_encode($data,true);
        }
        $rand=mt_rand(1,1000);
        $prize='未中奖';
        if($rand>=100&&$rand<=1){
            $prize="一等奖";
        }else if($rand>=200&&$rand<=300){
            $prize='二等奖';
        }else if($rand>=400&&$rand<=600){
            $prize='三等奖';
        }
        $data=[
            'error'=>0,
            'msg'=>'ok',
            'data'=>[
              'prize'=>$prize,
            ],
        ];
        return $data;
    }
}
