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

        if (!empty($data)) {
            ksort($data);
            $key = md5(implode("", $data));
            $data['key'] = $key;
            $user = Model("User")->where(["key" => $key])->find();

            // 是否存在用户
            if (!$user) {
                $id = Model("User")->insertGetId($data);
                $data['id'] = $id;
                $user = $data;
            }else{
                $user = $user->toArray();
            }

            //记录日志
            Model('UserLog')->create(['user_id' => $user['id']]);

            return $user;
        }

        return [];

    }

    /**
     * 重置用户（月初才执行）
     */
    public function resetUser($user){
        $lastMonth = date("m",strtotime($user['last_price_date']));
        $nowMonth = date("m");

        // 如果不是当前月(重置用户信息)
        if($lastMonth != $nowMonth){
            $data = ['month_price' => 0, 'last_price_date' => date("Y-m-d H:i:s")];
            Model('User')->where(["key" => $user['key']])->update($data);
            $user['month_price'] = $data['month_price'];
            $user['last_price_date'] = $data['last_price_date'];
        }

        return $user;
    }

    /**
     * 更新扣费金额
     * @param $user
     * @param $price
     * @return int|true
     * @throws \think\Exception
     */
    public function updatePrice($user,$price){
        return Model('User')->where(["key" => $user['key']])->setInc('month_price', $price);
    }

}