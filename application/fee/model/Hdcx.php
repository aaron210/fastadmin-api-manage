<?php

namespace app\fee\model;

use think\Cache;
use think\Model;


class Hdcx extends Model
{

    // 表名
    protected $name = 'hdcx';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];

    /**
     * 根据
     */
    public function checkProvinceByPhone($phone){
        $phone = substr($phone,0,7);
        $res = $this->where("phone", $phone)->find();
        return $res;
    }

    // 获取省份
    public function getProvince(){
        $cache = new Cache();
        $province = $cache->get('province');
        if(!$province){
            $province = self::group("province")->field("province")->select();
            $province = collection($province)->toArray();
            $cache->set('province',json_encode($province));
        }else{
            $province = json_decode($province);
        }
        return $province;
    }

}
