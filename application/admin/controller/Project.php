<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Model;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Project extends Backend
{
    
    /**
     * Project模型对象
     * @var \app\admin\model\Project
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->makeCache();
        $this->model = new \app\admin\model\Project;
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    
    private function makeCache(){
        $MProject = Model("Project");
        $res  = $MProject->select();
        $txt  = "<?php".PHP_EOL ;
        $txt .= "return [".PHP_EOL ;
        foreach ($res as $v){
            $txt .= '   "'.$v['ename'].'"=>"index/project",'.PHP_EOL;
        }
        $txt .= '];';
        file_put_contents(CONF_PATH."url.php",$txt);
    }

    public function makePreview(){

        // 获取参数
        // $data = input("get.");
        $data = $this->request->get("row/a");
        $charge_type = $data['charge_type'];
        $channel_number = $data['channel_number'];
        $instructions = $data['instructions'];

        $DataProcessing = Model('DataProcessing', 'logic');
        return $DataProcessing->makePreview($charge_type, $channel_number, $instructions);

    }
}