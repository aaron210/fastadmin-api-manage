<?php
/**
 * Created by PhpStorm.
 * User: wxj0707
 * Date: 2019/7/28
 * Time: 16:27
 */

namespace app\index\controller;


use app\common\controller\Frontend;
use fast\Http;
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

        // 判断这条信息是否已经存在
        if(isset($data['linkid'])&&!empty($data['linkid'])){
            $MParam = Model("Param");
            $resParam = $MParam->where(['linkid'=>$data['linkid']])->find();
            if($resParam){
                return $this->out("this linkid already exists");
            }
        }

        // 获取手机信息
        $location = $operator = $province = $city = "";
        $mobile = isset($data['mobile']) ? $data['mobile'] : "";
        if(!empty($mobile)){
            $phoneQCellCore = substr($mobile,0,7);
            $province = $city = $isp = $location = "";
            $Hdcx = Model("Hdcx");
            $lists = $Hdcx->where("phone", $phoneQCellCore)->find();
            if ($lists) {
                $province = $lists->province;
                $city = $lists->city;
                $operator = $lists->isp;
                $location = $province . $city . $isp;
            }
        }

        // json储存
        $Param = http_build_query($data , '' , '&');
        $saveData = [
            "param" => $Param,
            "project_id" => $res['id'],
            "mobile"     => isset($data['mobile']) ? $data['mobile'] : "",
            "linkid"     => isset($data['linkid']) ? $data['linkid'] : "",
            "location"   => $location,
            "operator"   => $operator,
            "province"   => $province,
            "city"       => $city,
            "mtime"      => date("Y-m-d H:i:s")
        ];
        $MParam = Model("Param");
        if( $MParam->save($saveData) ){
            return $this->out("ok");
        }else{
            return $this->out("false");
        }
    }

    /**
     * 输出
     * @param $data
     * @return \think\response\Json
     */
    private function out($data){
        return $data;
        return json([
            "status"=>$data
        ]);
    }

    public function test2()
    {
        $phone = "15919829112";
        $phoneQCellCore = substr($phone,0,7);
        $province = $city = $isp = $location = "";
        $Hdcx = Model("Hdcx");
        $res = $Hdcx->where("phone", $phoneQCellCore)->find();
        if ($res) {
            $province = $res->province;
            $city = $res->city;
            $isp = $res->isp;
            $location = $province . $city . $isp;
        }
        dump($province);
        dump($city);
        dump($isp);
        dump($location);
        dump(123123123);
    }

}