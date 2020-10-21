<?php

namespace App\IndexModel;

use Illuminate\Database\Eloquent\Model;

class LoginModel extends Model
{
    //设置表名
    protected $table="user";
    //设置主键
    protected $primaryKey="user_id";
    //设置时间戳
    public $timestamps=false;
}
