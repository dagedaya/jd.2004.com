<?php

namespace App\IndexModel;

use Illuminate\Database\Eloquent\Model;

class PrizeModel extends Model
{
    //设置表名
    protected $table="p_prize";
    //设置主键
    protected $primaryKey="prize_id";
    //设置时间戳
    public $timestamps=false;
}
