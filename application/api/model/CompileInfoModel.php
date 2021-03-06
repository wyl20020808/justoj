<?php
/**
 * Created by PhpStorm.
 * User: L. Jiang <l.jiang.1024@gmail.com>
 * Date: 2018/5/10
 * Time: 12:46
 */

namespace app\api\model;


use think\Model;

/**
 * Class CompileInfoModel
 *
 * @property string error
 * @property int solution_id
 *
 * @package app\api\model
 */
class CompileInfoModel extends Model
{
    protected $table = 'compileinfo';
}