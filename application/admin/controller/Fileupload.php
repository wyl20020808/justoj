<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/24
 * Time: 9:12
 */

namespace app\admin\controller;


use app\extra\controller\AdminBaseController;
use think\Request;

class Fileupload extends AdminBaseController
{
	public function __construct(Request $request = null)
	{
		parent::__construct($request);
		$this->assign('nav', 'upload_file');
	}

	public function index()
	{
		return view();
	}
}