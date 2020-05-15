<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use fast\Http;

class Index extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    public function index()
    {
        return json(["hello,world"]);
    }

    public function eeee(){
        $url = "http://mobsec-dianhua.baidu.com/dianhua_api/open/location?tel=15919829112";
        $ipData = Http::get($url);
        return $ipData;
    }

}
