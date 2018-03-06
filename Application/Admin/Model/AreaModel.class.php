<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/3
 * Time: 17:05
 */

namespace Admin\Model;


use Think\Model;

class AreaModel extends Model
{

    function getCount($where)
    {

        return $this->where($where)->field('count(*) as count')->find();

    }

}