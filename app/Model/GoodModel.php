<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class GoodModel extends Model
{
    //设置表名
    protected $table="p_goods";
    //设置主键id
    protected $primaryKey="goods_id";
    //设置时间戳
    public $timestamps=false;
}
