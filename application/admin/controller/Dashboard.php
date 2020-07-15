<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Cache;
use think\Config;

/**
 * 控制台
 *
 * @icon fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend
{

    /**
     * 查看
     */
    public function index()
    {
        $redis = Cache::store('redis')->handler();

        $seventtime = \fast\Date::unixtime('day', -24);
        $paylist = $createlist = [];
        for ($i = 0; $i <= 24; $i++)
        {
            $day = date("Y-m-d", $seventtime + ($i * 86400));
            $visits[$day] = (int)($redis->hget("statistics:total_visits",$day) ?: 0);
            $output[$day] = (int)($redis->hget("statistics:total_output_today",$day) ?: 0);
            $return[$day] = (int)($redis->hget("statistics:total_return_today",$day) ?: 0);
        }

        $hooks = config('addons.hooks');
        $uploadmode = isset($hooks['upload_config_init']) && $hooks['upload_config_init'] ? implode(',', $hooks['upload_config_init']) : 'local';
        $addonComposerCfg = ROOT_PATH . '/vendor/karsonzhang/fastadmin-addons/composer.json';
        Config::parse($addonComposerCfg, "json", "composer");
        $config = Config::get("composer");
        $addonVersion = isset($config['version']) ? $config['version'] : __('Unknown');


        $total_visits = $redis->hget("statistics:total_visits",date("Y-m-d"));  // 记录访问总数
        $total_output_today = $redis->hget("statistics:total_output_today",date("Y-m-d"));  // 记录输出总数
        $total_return_today = $redis->hget("statistics:total_return_today",date("Y-m-d"));  // 记录回调总数

        $this->view->assign([
            'visits'          => $visits,
            'output'       => $output,
            'return'       => $return,
            'total_visits'     => $total_visits ?: 0,
            'total_output_today'     => $total_output_today ?: 0,
            'total_return_today'     => $total_return_today ?: 0,
        ]);

        return $this->view->fetch();
    }

    /**
     * 获取实时数据
     */
    public function data(){

        $redis = Cache::store('redis')->handler();
        $total_visits = $redis->hget("statistics:total_visits",date("Y-m-d"));  // 记录总数
        $total_output_today = $redis->hget("statistics:total_output_today",date("Y-m-d"));  // 记录输出总数
        $total_return_today = $redis->hget("statistics:total_return_today",date("Y-m-d"));  // 记录回调总数

        // 数据
        $data = [
            "total_visits"           => $total_visits  ?: 0,
            'total_output_today'     => $total_output_today ?: 0,
            'total_return_today'     => $total_return_today ?: 0,
        ];

        return ["code" => 200, "data" => $data];
    }

    /**
     * 根据小时统计按小时分布流量
     */
    public function dateHourStatistics(){
        for($i=1;$i<24;$i++){
           $timeData[] = date("YmdH", strtotime("-$i hour"));
        }
        dump($timeData);
    }

}
