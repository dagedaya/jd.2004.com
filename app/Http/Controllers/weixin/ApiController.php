<?php

namespace App\Http\Controllers\weixin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function __construct()
    {
        app('debugbar')->disable();//关闭调试
    }
    //接口获取数据（并渲染视图）
    public function test(){
//        echo '<pre>';print_r($_POST);echo '</pre>';
//        echo '<pre>';print_r($_GET);echo '</pre>';
        $goods_info=[
            'goods_id'=>'217',
            'goods_name'=>'iphone',
            'shop_price'=>12.34
        ];
        echo json_encode($goods_info,JSON_UNESCAPED_UNICODE);
    }
}
