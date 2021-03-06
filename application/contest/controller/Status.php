<?php


namespace app\contest\controller;


use app\api\model\ContestProblemModel;
use app\api\model\ProblemModel;
use app\api\model\SimModel;
use app\api\model\SolutionModel;
use app\contest\common\ContestBaseController;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;

class Status extends ContestBaseController {
    /**
     * 比赛内搜索
     *
     * @param $id
     * @param string $run_id
     * @param string $username
     * @param string $problem_id
     * @param string $result
     * @param string $language
     * @return \think\response\View
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function show_status_list($id, $run_id = '', $username = '', $problem_id = '', $result = '', $language = '') {
        /* 判断比赛是否开始 */
        if (!$this->contest_started) {
            $this->redirect("/contests/{$this->contest_id}");
        }


        if (!is_numeric($run_id)) {
            $run_id = '';
        } else {
            $run_id = intval($run_id);
        }

        if (!is_numeric($result)) {
            $result = '';
        } else {
            $result = intval($result);
        }

        if (!is_numeric($language)) {
            $language = '';
        } else {
            $language = intval($language);
        }

        $this->assign('run_id', htmlspecialchars($run_id));
        $this->assign('username', htmlspecialchars($username));
        $this->assign('problem_id', htmlspecialchars($problem_id));
        $this->assign('result', $result);
        $this->assign('language', $language);

        $this->assign('allowed_langs', $this->allowed_langs());

        // 获取题目列表
        $contest_problems = (new ContestProblemModel())->where('contest_id', $this->contest->contest_id)->order('num', 'asc')->select();
        // 获取题目ids
        $contest_problem_ids = [];

        foreach ($contest_problems as $contest_problem) {
            $contest_problem->problem = (new ProblemModel())->where(['problem_id' => $contest_problem->problem_id])->find();
            $contest_problem_ids[] = $contest_problem->problem_id;
        }
        $this->assign('contest_problems', $contest_problems);

        // 构建problem_id 真实到虚拟映射
        $problem_id_true_to_fake = [];
        foreach ($contest_problems as $contest_problem) {
            $problem_id_true_to_fake[$contest_problem->problem_id] = $contest_problem->num;
        }
        $this->assign('problem_id_true_to_fake', $problem_id_true_to_fake);

        // 指明solution_id
        if ('' != $run_id) {
            $solutions = (new SolutionModel)
                ->where('solution_id', $run_id)
                ->where('contest_id', $this->contest_id)
                ->paginate(10);
            foreach ($solutions as $solution) {
                $solution->sim = (new SimModel())->where('s_id', $solution->solution_id)->find();
            }
            $solutions->appends('run_id', $run_id);
            $this->assign('solutions', $solutions);
            return view('./contest-status');
        }
        // 获取所有题目列表

        // 搜索
        $where = (new SolutionModel());
        $where = $where->where(['contest_id' => $this->contest->contest_id])->where(['problem_id' => ['in', $contest_problem_ids]]);
        if ('' != $username) {
            $where = $where->where(['user_id' => $username]);
        }
        if ('' != $problem_id) {
            $problem_id_true = ContestProblemModel::get(['contest_id' => $this->contest->contest_id, 'num' => $problem_id])->problem_id;
            $where = $where->where(['problem_id' => $problem_id_true]);
        }
        if ('' != $result) {
            $where = $where->where(['result' => $result]);
        }
        if ('' != $language) {
            $where = $where->where(['language' => $language]);
        }
        $solutions = $where->order('solution_id', 'desc')->paginate(10);

        foreach ($solutions as $solution) {
            $solution->sim = (new SimModel())->where('s_id', $solution->solution_id)->find();
        }

        $solutions->appends('id', $this->contest->contest_id);
        $solutions->appends('run_id', '');
        $solutions->appends('username', $username);
        $solutions->appends('problem_id', $problem_id);
        $solutions->appends('result', $result);
        $solutions->appends('language', $language);

        $this->assign('solutions', $solutions);
        $this->assign('nav', 'status');
        return view('./contest-status');
    }
}