<?php

namespace app\admin\model;

use think\Model;


class Project extends Model
{

    // 表名
    protected $name = 'project';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'datetime';

    // 定义时间戳字段名
    protected $createTime = "mtime";
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];

    /**
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getProjectData(){
        $project = self::select();
        $project = collection($project)->toArray();
        $projectData = [];
        foreach ($project as $v){
            $projectData[$v['id']] = $v['name'];
        }
        $projectData = json_encode($projectData);
        return $projectData;
    }


}
