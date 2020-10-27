<?php

namespace App\IndexModel;

use Illuminate\Database\Eloquent\Model;

class MovieModel extends Model
{
    //设置表名
    protected $table="p_movie";
    //设置主键
    protected $primaryKey="movie_id";
    //设置时间戳
    public $timestamps=false;
}
