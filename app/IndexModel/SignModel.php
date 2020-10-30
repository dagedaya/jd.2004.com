<?php

namespace App\IndexModel;

use Illuminate\Database\Eloquent\Model;

class SignModel extends Model
{
    //设置表名
    protected $table="p_sign";
    //设置主键
    protected $primaryKey="sing_id";
    //设置时间戳
    public $timestamps=false;
}
