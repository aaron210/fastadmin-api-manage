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
use think\Cache;
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
            $this->markNum($data, $province);
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

    /**
     * 记录数量
     */
    private function markNum($data, $province)
    {

        // 获取通道号码
        if(isset($data['spcode'])){
            $channel_number = $data['spcode'];
        }elseif(isset($data['spnumber'])){
            $channel_number = $data['spnumber'];
        }elseif(isset($data['calledid'])){
            $channel_number = $data['calledid'];
        }else{
            $channel_number = "";
        }

        // 记录数量
        if($channel_number){

            // 转换拼音
            $PinyinLogic = Model('Pinyin', 'logic', false, "index");
            $provincePinyin = $PinyinLogic->encode($province,'all');

            // 生成缓存
            $redis = Cache::store('redis')->handler();
            $task = $redis->get("channel:" . $channel_number . ":" . $provincePinyin);
            if($task){
                $task = json_decode($task,true);
                $redis->hincrby("channel_total_daily:" . $task['id'], date("Ymd"), 1); // 加一日志
            }
        }

    }

}