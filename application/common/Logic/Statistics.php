<?php

namespace app\common\Logic;

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

}
