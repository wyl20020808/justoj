<?php


namespace app\home\controller;


use app\api\model\CompileInfoModel;
use app\api\model\SolutionModel;
use app\api\model\SourceCodeModel;
use app\extra\controller\UserBaseController;
use think\response\View;

class Solution extends UserBaseController {

    /**
     * 显示提交结果
     *
     * @param string $solution_id
     * @return View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function show_detail($solution_id) {
        intercept('' == $solution_id, 'invalid');
        /* @var $solution SolutionModel */
        $solution = (new SolutionModel())->where('solution_id', $solution_id)->find();
        intercept(null == $solution, 'invalid');

        // 检查是否有访问权限
        if (!($this->is_administrator || ($this->login_user && $solution->user_id == $this->login_user->user_id))) {
            return $this->lang['do_not_have_privilege'];
        }

        $solution->fk();
        $solution->result_text = $this->lang[$solution->result_code];
        $source_code = (new SourceCodeModel())->where('solution_id', $solution_id)->find();
        intercept(null == $source_code, 'invalid');
        $source_code->source = htmlspecialchars($source_code->source);

        /* @var $compile_info CompileInfoModel */
        $compile_info = CompileInfoModel::get(['solution_id' => $solution_id]);
        if ($compile_info) {
            $compile_info->error = htmlspecialchars($compile_info->error);
        }

        return view($this->theme_root . '/status-solution', [
            'solution' => $solution,
            'source_code' => $source_code,
            'result_map' => SolutionModel::$result_map,
            'compile_info' => $compile_info
        ]);
    }

    /**
     * 显示提交结果表格部分
     *
     * @param $solution_id
     * @return mixed|View
     */
    public function show_table_part($solution_id) {
        intercept('' == $solution_id, 'invalid');
        $solution = (new SolutionModel())->where('solution_id', $solution_id)->find();
        intercept(null == $solution, 'invalid');

        // 检查是否有访问权限
        if (!($this->is_administrator || ($this->login_user && $solution->user_id == $this->login_user->user_id))) {
            return $this->lang['do_not_have_privilege'];
        }

        $solution->fk();
        $solution->result_text = $this->lang[$solution->result_code];
        return view($this->theme_root . '/status-solution-table-part', [
            'solution' => $solution
        ]);
    }
}