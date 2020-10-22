<?php

namespace App\IndexModel;

use Illuminate\Database\Eloquent\Model;

class CollectModel extends Model
{
    //设置表名
    protected $table="p_collect";
    //设置主键
    protected $primaryKey="collect_id";
    //设置时间戳
    public $timestamps=false;
}
