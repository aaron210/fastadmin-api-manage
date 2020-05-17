<?php
namespace app\fee\controller;

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
    public function update(){
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
                    Model("Success")->insert(["phone" => $phone, "type" => 3, "uid" => $data['uid']]);
                    return "1KW1002DH?1?ehwwhwdh?XL9?3?" . $data['uid'] . "?60?0?0";
                }
                if ($res->province == "广东" && !in_array($res->city,['广州','河源']) && $res->isp == '移动'){
                    Model("Success")->insert(["phone" => $phone, "type" => 4, "uid" => $data['uid']]);
                    // return "1KW1002DH?1?ehwwhsel?DZ14?2?1?360?0?<>1KW1003DH?回复“?”或?信息费?中国移动?";
                    return "1KW1002DH?1?ehwwhsel?DZ14?1?" . $data['uid'] . "?360?0?<>1KW1003DH?回复“?”或?信息费?中国移动?";
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
        $phone = '13600306638';
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