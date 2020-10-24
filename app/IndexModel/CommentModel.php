<?php

namespace App\IndexModel;

use Illuminate\Database\Eloquent\Model;

class CommentModel extends Model
{
    //设置表名
    protected $table="goods_comment";
    //设置主键
    protected $primaryKey="comment_id";
    //设置时间戳
    public $timestamps=false;
}
