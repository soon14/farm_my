<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/19
 * Time: 14:23
 */

namespace Home\Model;


use Think\Model;

class BeforeModel extends Model
{
    public function getData(){

        return $this->where(['userid'=>session('user.id')])->find();

    }

}