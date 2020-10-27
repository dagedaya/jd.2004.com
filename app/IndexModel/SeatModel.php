<?php

namespace App\IndexModel;

use Illuminate\Database\Eloquent\Model;

class SeatModel extends Model
{
    //设置表名
    protected $table="p_seat";
    //设置主键
    protected $primaryKey="seat_id";
    //设置时间戳
    public $timestamps=false;
}
