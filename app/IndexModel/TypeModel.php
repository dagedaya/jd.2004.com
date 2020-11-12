<?php

namespace App\IndexModel;

use Illuminate\Database\Eloquent\Model;

class TypeModel extends Model
{
    //设置表名
    protected $table="p_coupon_type";
    //设置主键
    protected $primaryKey="coupon_id";
    //设置时间戳
    public $timestamps=false;
}
