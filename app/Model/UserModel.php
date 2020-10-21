<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserModel extends Model
{
    //设置表名
    protected $table="p_users";
    //设置主键id
    protected $primaryKey="user_id";
    //设置时间戳
    public $timestamps=false;
}
