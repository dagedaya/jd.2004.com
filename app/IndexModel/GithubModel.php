<?php

namespace App\IndexModel;

use Illuminate\Database\Eloquent\Model;

class GithubModel extends Model
{
    //设置表名
    protected $table="p_users_github";
    //设置时间戳
    public $timestamps=false;
}
