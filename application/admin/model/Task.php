<?php

namespace app\admin\model;

use think\Model;


class Task extends Model
{

    // 表名
    protected $name = 'task';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = "mtime";
    protected $deleteTime = false;

    
    public function getOperatorsList()
    {
        return ['yidong' => __('移动')];
    }


}
