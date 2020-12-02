<?php

namespace App\IndexModel;

use Illuminate\Database\Eloquent\Model;

class HistoryModel extends Model
{
    //设置表名
    protected $table="p_wx_media";
    //设置主键
    protected $primaryKey="id";
    //设置时间戳
    public $timestamps=false;
}
