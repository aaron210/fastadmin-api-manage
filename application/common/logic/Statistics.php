<?php

namespace app\common\logic;

use think\Cache;
use think\Model;

class Statistics extends Model
{

    /**
     * 统计当日
     */

    public function run(){
        $redis = Cache::store('redis')->handler();
        $redis->hincrby("statistics:total_visits",date("Y-m-d"),1);  // 记录总数
    }

    /**
     * 输出总数
     */
    public function total_output_today(){
        $redis = Cache::store('redis')->handler();
        $redis->hincrby("statistics:total_output_today",date("Y-m-d"),1);  // 记录总数
    }

    /**
     * 回调总数
     */
    public function total_return_today(){
        $redis = Cache::store('redis')->handler();
        $redis->hincrby("statistics:total_return_today",date("Y-m-d"),1);  // 记录总数
    }

    /**
     * 省份统计
     */
    public function set_province_statistics($province){
        $redis = Cache::store('redis')->handler();
        $redis->hincrby("statistics:province_statistics:" . date("Y-m-d"), $province, 1);  // 记录总数
    }

}
