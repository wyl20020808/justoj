<?php


namespace app\api\controller;


use app\api\common\ApiBaseController;
use app\api\model\JudgeClientModel;
use think\Env;

class JudgeClient extends ApiBaseController {

    /**
     * 更新客户端 justoj-data 数据 git hash 信息
     *
     * @param string $secure_code
     * @param string $client_name
     * @param string $data_git_hash
     *
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function push_data_info($secure_code = '', $client_name = '', $data_git_hash = '') {
        if ($secure_code != Env::get('config.secure_code')) {
            return json(['code' => 500, 'msg' => 'Invalid secure code.']);
        }

        if (!$client_name) {
            return json(['code' => 500, 'msg' => 'Client name can NOT be empty.']);
        }

        if (!$data_git_hash) {
            return json(['code' => 500, 'msg' => 'Data git hash can NOT be empty.']);
        }

        $judge_client = (new JudgeClientModel())->where(['client_name' => $client_name])->find();
        if (!$judge_client) {
            $judge_client = (new JudgeClientModel());
            $judge_client->client_name = $client_name;
            $judge_client->data_git_hash = '';
        }

        if ($data_git_hash != $judge_client->data_git_hash) {
            $judge_client->data_git_hash = $data_git_hash;
            $judge_client->save();
        }

        return json(['code' => 0, 'msg' => 'ok']);
    }
}