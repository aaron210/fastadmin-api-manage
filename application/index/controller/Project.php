<?php
/**
 * Created by PhpStorm.
 * User: wxj0707
 * Date: 2019/7/28
 * Time: 16:27
 */

namespace app\index\controller;


use app\common\controller\Frontend;
use think\Model;
use think\Request;

class Project extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    public function index(Request $request){

        // 获取参数
        $data = input("get.");
        if(empty($data)){
            return $this->out("empty");
        }

        // 获取是否存在这个项目
        $Model = Model("Project");
        $res = $Model->where([
            "ename"=>$request->path()
        ])->find();
        if(empty($res)){
            return $this->out("error");
        }

        // json储存
        $Param = json_encode($data);
        $saveData = [
            "param" => $Param,
            "project_id" => $res['id'],
            "mobile" => isset($data['mobile']) ? $data['mobile'] : "",
            "mtime" => date("Y-m-d H:i:s")
        ];
        $MParam = Model("Param");
        if( $MParam->save($saveData) ){
            return $this->out("ok");
        }else{
            return $this->out("false");
        }
    }

    private function out($data){
        return json([
            "status"=>$data
        ]);
    }

}