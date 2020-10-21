<?php

namespace App\IndexModel;

use Illuminate\Database\Eloquent\Model;

class CartModel extends Model
{
    //设置表名
    protected $table="p_cart";
    //设置时间戳
    public $timestamps=false;
}
