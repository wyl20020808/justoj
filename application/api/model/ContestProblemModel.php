<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/9
 * Time: 8:42 PM
 */

namespace app\api\model;


use think\Db;
use think\Exception;
use think\Model;

class ContestProblemModel extends Model
{
	protected $table = 'contest_problem';
	public function fk()
	{
		$this->problem = ProblemModel::get(['problem_id' => $this->problem_id]);
		// 获得这场比赛中AC题目的人数
		$this->problem->ac_cnt = Db::query("select count(solution_id) as cnt from solution where contest_id=".$this->contest_id." and problem_id=".$this->problem->problem_id." and result=4")[0]["cnt"];
		$this->problem->submit_cnt = Db::query("select count(solution_id) as cnt from solution where contest_id=".$this->contest_id." and problem_id=".$this->problem->problem_id)[0]["cnt"];
	}
}