<?php

namespace App\IndexModel;

use Illuminate\Database\Eloquent\Model;

class GoodsModel extends Model
{
    //设置表名
    protected $table="p_goods";
    //设置主键
    protected $primaryKey="goods_id";
    //设置时间戳
    public $timestamps=false;
}
