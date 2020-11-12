<?php

namespace App\IndexModel;

use Illuminate\Database\Eloquent\Model;

class CouponModel extends Model
{
    //设置表名
    protected $table="p_coupon";
    //设置主键
    protected $primaryKey="id";
    //设置时间戳
    public $timestamps=false;
}
