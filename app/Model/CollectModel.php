<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CollectModel extends Model
{
    //设置表名
    protected $table="p_xcx_collect";
    //设置主键id
    protected $primaryKey="id";
    //设置时间戳
    public $timestamps=false;
}
