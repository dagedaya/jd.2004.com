<?php

namespace App\Model;

use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;
use Illuminate\Database\Eloquent\Model;

class CategoryModel extends Model
{
    use ModelTree, AdminBuilder;
    //设置表名
    protected $table="p_category";
    //设置主键id
    protected $primaryKey="cat_id";
    //设置时间戳
    public $timestamps=false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        //修改成自己的字段名
        $this->setParentColumn('parent_id');
        $this->setOrderColumn('cat_desc');
        $this->setTitleColumn('cat_name');
    }
}
