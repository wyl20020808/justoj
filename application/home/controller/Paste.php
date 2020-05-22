<?php


namespace app\home\controller;


use app\api\model\PasteModel;
use app\extra\controller\UserBaseController;

class Paste extends UserBaseController {
    /**
     * Paste
     * @param string $id
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index($id = '') {
        $this->assign('nav', 'paste');
        if ('' == $id) {
            $this->assign('allowed_langs', paste_allowed_langs());
            return view($this->theme_root . '/paste');
        }

        $paste = (new PasteModel())->where('id', $id)->find();
        intercept(null == $paste, '代码不存在');

        $paste->code = htmlspecialchars($paste->code);
        $paste->lang_text = paste_allowed_langs()[$paste->lang];

        $this->assign('paste', $paste);
        return view($this->theme_root . '/paste-detail');
    }
}