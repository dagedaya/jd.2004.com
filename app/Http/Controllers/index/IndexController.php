<?php

namespace App\Http\Controllers\index;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    //前台首页
    public function index(){
        return view('index/index');
    }
    //退出
    public function exit(Request $request){
        session(['user_id'=>null,'user_name'=>null,'user_email'=>null,'user_tel'=>null]);
        $user_id=$request->session()->get('user_id');
        if(empty($user_id)){
            return redirect('login/login')->with('msg','退出完成');
        }
    }
}
