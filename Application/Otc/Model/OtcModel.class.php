<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/4
 * Time: 15:44
 */

/**
 * 初始化数据库 链接
 * Class OtcModel
 */
namespace Otc\Model;
class OtcModel extends \Think\Model
{
    protected $tablePrefix = 'tw_';
    protected $connection = [
        'db_type'  => 'mysql',
        'db_user'  => 'root',
        'db_pwd'   => 'root',
        'db_host'  => '127.0.0.1',
        'db_port'  => '3306',
        'db_name'  => 'otc',
        'db_charset' => 'utf8',
    ];

}