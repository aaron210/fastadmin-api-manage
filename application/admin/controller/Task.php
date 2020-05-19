<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Cache;
use think\Db;
use think\Exception;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Task extends Backend
{
    
    /**
     * Task模型对象
     * @var \app\admin\model\Task
     */
    protected $model = null;
    protected $multiFields = 'isstart';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Task;
        $this->view->assign("operatorsList", $this->model->getOperatorsList());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }

        $province = Model("Hdcx")->getProvince();
        $province = array_column($province, 'province');
        $this->view->assign("province", json_encode($province)); // 获取省份

        $this->assign("projectData",Model("Project")->getProjectData());
        return $this->view->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);

                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                        $this->model->validateFailException(true)->validate($validate);
                    }
                    $result = $this->model->allowField(true)->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->makeRedisCache();
                    $this->success();
                } else {
                    $this->error(__('No rows were inserted'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }

        $province = Model("Hdcx")->getProvince();
        $province = array_column($province, 'province');
        $this->view->assign("province", $province); // 获取省份

        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validateFailException(true)->validate($validate);
                    }
                    $result = $row->allowField(true)->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->makeRedisCache();
                    $this->success();
                } else {
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }

        $province = Model("Hdcx")->getProvince();
        $province = array_column($province, 'province');

        $this->view->assign("row", $row);
        $this->view->assign("province", $province); // 获取省份
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * 批量更新
     */
    public function multi($ids = "")
    {
        $ids = $ids ? $ids : $this->request->param("ids");
        if ($ids) {
            if ($this->request->has('params')) {
                parse_str($this->request->post("params"), $values);
                $values = array_intersect_key($values, array_flip(is_array($this->multiFields) ? $this->multiFields : explode(',', $this->multiFields)));
                if ($values || $this->auth->isSuperAdmin()) {
                    $adminIds = $this->getDataLimitAdminIds();
                    if (is_array($adminIds)) {
                        $this->model->where($this->dataLimitField, 'in', $adminIds);
                    }
                    $count = 0;
                    Db::startTrans();
                    try {
                        $list = $this->model->where($this->model->getPk(), 'in', $ids)->select();
                        foreach ($list as $index => $item) {
                            $count += $item->allowField(true)->isUpdate(true)->save($values);
                        }
                        Db::commit();
                    } catch (PDOException $e) {
                        Db::rollback();
                        $this->error($e->getMessage());
                    } catch (Exception $e) {
                        Db::rollback();
                        $this->error($e->getMessage());
                    }
                    if ($count) {
                        $this->makeRedisCache();
                        $this->success();
                    } else {
                        $this->error(__('No rows were updated'));
                    }
                } else {
                    $this->error(__('You have no permission'));
                }
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }

    // 生成缓存
    private function makeRedisCache(){

        $task = Model("task")->select();
        $task = collection($task)->toArray();

        // 获取城市列表
        $province = Model("Hdcx")->getProvince();

        // 生成缓存
        $redis = Cache::store('redis')->handler();
        $redisKeyLists = $redis->keys("projet:*");
        foreach($redisKeyLists as $v){
            $redis->del($v);
        }

        // 处理数据
        foreach($task as $v){
            if($v['province']!=""){
                // 转换拼音
                $PinyinLogic = Model('Pinyin', 'logic');
                $provincePinyin = $PinyinLogic->encode($province[$v['province']]->province,'all');


                $redis->hset("projet:" . $provincePinyin, $v['id'], json_encode($v));
            }
        }
    }

    public function makePreview(){

        // 获取参数
        // $data = input("get.");
        $data = $this->request->get("row/a");
        $charge_type = $data['charge_type'];
        $channel_number = $data['channel_number'];
        $instructions = $data['instructions'];
        $send_num = $data['send_num'];
        $interval_time = $data['interval_time'];
        $desc = $data['desc'];

        $DataProcessing = Model('DataProcessing', 'logic');
        return $DataProcessing->makePreview($charge_type, $channel_number, $instructions, $send_num, $interval_time) . $desc;

    }
}
