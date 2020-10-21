<?php

namespace App\IndexModel;

use Illuminate\Database\Eloquent\Model;

class Orders_GoodsModle extends Model
{
    //设置表名
    protected $table="p_order_goods";
    //设置时间戳
    public $timestamps=false;
}
