<?php

namespace app\admin\Logic;

use think\Model;

class DataProcessing extends Model
{

    /**
     * 手机加密
     * @param $flag1
     * @return string|string[]
     */
    public function encodePhone($flag1){
        $flag1 = str_replace("0","h", $flag1);
        $flag1 = str_replace("1","e", $flag1);
        $flag1 = str_replace("2","l", $flag1);
        $flag1 = str_replace("3","o", $flag1);
        $flag1 = str_replace("4","i", $flag1);
        $flag1 = str_replace("5","s", $flag1);
        $flag1 = str_replace("6","w", $flag1);
        $flag1 = str_replace("7","a", $flag1);
        $flag1 = str_replace("8","r", $flag1);
        $flag1 = str_replace("9","d", $flag1);
        return $flag1;
    }

    /**
     * 手机解密
     * @param $flag1
     * @return string|string[]
     */
    public function decodePhone($flag1){
        $flag1 = str_replace("h", "0",$flag1);
        $flag1 = str_replace("e", "1",$flag1);
        $flag1 = str_replace("l", "2",$flag1);
        $flag1 = str_replace("o", "3",$flag1);
        $flag1 = str_replace("i", "4",$flag1);
        $flag1 = str_replace("s", "5",$flag1);
        $flag1 = str_replace("w", "6",$flag1);
        $flag1 = str_replace("a", "7",$flag1);
        $flag1 = str_replace("r", "8",$flag1);
        $flag1 = str_replace("d", "9",$flag1);
        return $flag1;
    }

    /**
     * 格式化手机号码
     * @param $phone
     * @return false|string|string[]|null
     */
    public function formatPhoneNumber($phone){
        if ($phone == null) {
            return null;
        }
        $phone = trim($phone);
        $phone = $this->decodePhone($phone);
        if ($this->startsWith($phone,"86")) {
            $phone = substr($phone,2);
        }
        if (($this->startsWith($phone,"+86")) || ($this->startsWith($phone,"086"))) {
            $phone = substr($phone,3);
        }
        if (($this->startsWith($phone,"+086")) || ($this->startsWith($phone,"0086"))) {
            $phone = substr($phone,4);
        }
        if (($this->startsWith($phone,"130")) || ($this->startsWith($phone,"138")))
        {
            if (strlen($phone) == 8) {
                $phone = $phone."500";
            } else if (strlen($phone) == 9) {
                $phone = $phone."00";
            } else if (strlen($phone) == 10) {
                $phone = $phone."0";
            } else if (strlen($phone) > 11) {
                $phone = substr($phone,0, 11);
            }
            if (strlen($phone) == 11) {
                $phone = substr($phone,0, 8) . "500";
            }
        }
        return $phone;
    }

    /**
     * 判断开头字符与java startsWith相同
     * @param $haystack
     * @param $needle
     * @return bool
     */
    public function startsWith($haystack, $needle){
        return strncmp($haystack, $needle, strlen($needle)) === 0;
    }

    /**
     * SMS预览
     * @param $charge_type
     * @param $channel_number
     * @param $instructions
     * @return
     */
    public function makePreview($charge_type, $channel_number, $instructions)
    {
        // 数据加密
        $channel_number = $this->encodePhone($channel_number);
        // $instructions = $this->encodePhone($instructions); // 不需要加密

        // 组合短信
        $sms = "1KW1002DH?" . $charge_type . "?" . $channel_number . "?" . $instructions . "?3?uid?60?0?0";
        return $sms;
    }


}
