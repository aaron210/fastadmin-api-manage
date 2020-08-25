<?php


namespace app\common\logic;

use think\Model;


class User extends Model
{

    /**
     * 检查用户身份
     */
    public function checkUser()
    {

        // 获取参数
        $data = input("get.");
        $user = Model("User")->where($data)->find();

        // 是否存在用户
        if (!$user) {
            $id = Model("User")->insertGetId($data);
            $data['id'] = $id;
            $user = $data;
        }

        //记录日志
        Model('UserLog')->create(['user_id' => $user['id']]);

        return $user;

    }

}