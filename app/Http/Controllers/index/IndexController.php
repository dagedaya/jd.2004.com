<?php

namespace App\Http\Controllers\index;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\IndexModel\GoodsModel;
use Monolog\Processor\GitProcessor;
use Illuminate\Pagination\Paginator;

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
            return redirect('/login')->with('msg','退出完成');
        }
    }
    //首页搜索
    public function search(Request $request){
        $goods_name=$request->goods_name;
        $search=GoodsModel::where('goods_name','like',"%".$goods_name."%")->paginate(10);
        if(is_object($search)){
            $search->toArray();
        }
        //分页保留条件
        $page = isset($page)?$request['page']:1;
        $taskList = $search->appends(array(
            'goods_name'=>$goods_name,
            'page'=>$page
            //add more element if you have more search terms
        ));
        return view('index/search',['search'=>$search]);
    }
}
