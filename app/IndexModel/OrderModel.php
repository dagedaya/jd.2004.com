<?php

namespace App\IndexModel;

use Illuminate\Database\Eloquent\Model;

class OrderModel extends Model
{
    //设置表名
    protected $table="order_info";
    //设置时间戳
    public $timestamps=false;
}
