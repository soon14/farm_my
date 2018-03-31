<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/31
 * Time: 10:19
 */

namespace Admin\Model;


class UserpropertyModel
{


    function getPageList(){

       return $this->where(['award'=>['gt',0],'bonus_time'=>['neq',date('Y-m-d',time())]])->limit(0,1000)->select();

    }




}