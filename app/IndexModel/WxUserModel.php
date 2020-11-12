<?php

namespace App\IndexModel;

use Illuminate\Database\Eloquent\Model;

class WxUserModel extends Model
{
    //设置表名
    protected $table="wxuser";
    //设置主键
    protected $primaryKey="id";
    //设置时间戳
    public $timestamps=false;
}
