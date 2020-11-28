<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/7
 * Time: 20:47
 */

namespace app\extra\controller;


use app\api\model\UiLanuageModel;
use app\api\model\UserModel;
use think\Config;
use think\Controller;
use think\Env;
use think\Request;
use think\Session;

class BaseController extends Controller {
    public $is_administrator;
    public $is_root;
    /* @var $login_user UserModel */
    public $login_user;
    public $is_login;

    public $site_name;

    public $show_ui_lang;
    public $lang;

    public $show_browser_banner;

    public $theme_root = 'home@themes/bootstrap';


    /**
     * 获取可以使用的语言列表
     */
    public function allowed_langs() {
        $tmps = Config::get('langs');
        $allowed_langs = [];
        foreach ($tmps as $tmp) {
            if ($tmp['allowed']) {
                $allowed_langs[] = $tmp;
            }
        }
        return $allowed_langs;
    }

    /**
     * BaseController constructor.
     * @param Request|null $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function __construct(Request $request = null) {
        parent::__construct($request);

        $this->site_name = Env::get('config.site_name', 'JustOJ');
        $this->assign('site_name', $this->site_name);

        $this->theme_root = 'home@themes/bootstrap';

        $this->assign('admin_email', Env::get('config.admin_email'));
        $this->assign('theme_root', $this->theme_root);

        // 判断User-Agent
        $this->show_browser_banner = false;
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        if (
            strpos($user_agent, "QIHOO")
            || strpos($user_agent, "MSIE 10")
            || strpos($user_agent, "MSIE 9")
            || strpos($user_agent, "MSIE 8")
            || strpos($user_agent, "MSIE 7")
            || strpos($user_agent, "MSIE 6")
            || strpos($user_agent, "MSIE 5")
        ) {
            $this->show_browser_banner = true;
        }
        $this->assign('show_browser_banner', $this->show_browser_banner);

        // 初始化当前登录用户变量
        $this->login_user = null;
        $this->is_login = false;
        if (session('user')) {
            $this->login_user = session('user');
            $this->login_user = (new UserModel())->where(['user_id' => $this->login_user->user_id])->find();
            $this->is_login = true;
        }
        $this->assign('is_login', $this->is_login);
        $this->assign('login_user', $this->login_user);

        // 赋予管理员权限
        $this->is_administrator = false;
        if (Session::get('administrator')) $this->is_administrator = true;
        $this->assign('is_administrator', $this->is_administrator);

        // 赋予超级管理员root权限
        $this->is_root = false;
        if (Session::get('root')) $this->is_root = true;
        $this->assign('is_root', $this->is_root);

        // 设置用户UI语言
        $dicts = Config::get('lang_dict');
        $this->show_ui_lang = 'cn'; // 默认语言
        if (!$this->login_user) {
            if (Session::get('ui_language')) $this->show_ui_lang = Session::get('ui_language');
        } else {
            $ui_language_obj = UiLanuageModel::get(['user_id' => $this->login_user->user_id]);
            if (!$ui_language_obj) {
                $ui_language_obj = new UiLanuageModel();
                $ui_language_obj->user_id = $this->login_user->user_id;
                $ui_language_obj->language = 'en';
                $ui_language_obj->save();
            }
            $this->show_ui_lang = $ui_language_obj->language;
        }
        $this->lang = array();
        $this->assign('show_ui_lang', $this->show_ui_lang);
        foreach ($dicts as $key => $dict) {
            $this->lang[$key] = $dict[$this->show_ui_lang];
        }
        $this->assign('lang', $this->lang);

        // 初始化nav变量控制导航栏class="active"
        $this->assign('nav', 'home');

        $this->assign('need_edit_profile', false);
        if ($this->login_user && UserModel::need_complete_info((new UserModel())->where(['user_id' => $this->login_user->user_id])->find())) {
            $this->assign('need_edit_profile', true);
        }
    }

    public function need_root($type = 'json') {
        if (!$this->is_root) {
            if ('json' == $type) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'msg' => $this->lang['do_not_have_privilege']]);
            } else {
                echo $this->lang['do_not_have_privilege'];
            }
            die();
        }
    }

    public function need_admin($type = 'json') {
        if (!$this->is_administrator) {
            if ('json' == $type) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'msg' => $this->lang['do_not_have_privilege']]);
            } else {
                echo $this->lang['do_not_have_privilege'];
            }
            die();
        }
    }

    public function need_login($type = 'json') {
        if (!$this->login_user) {
            if ('json' == $type) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'msg' => $this->lang['not_login']]);
            } else {
                echo $this->lang['not_login'];
            }
            die();
        }
    }
}