<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/31
 * Time: 10:19
 */

namespace Admin\Model;


use Think\Model;

class UserpropertyModel extends Model
{


    function getPageList(){

        $where['award'] = ['gt',0];
        $where['cmc'] = ['gt',0];
        $where['_logic'] = 'or';
        $map['_complex'] = $where;
        $map['bonus_time'] =  ['neq',date('Y-m-d',time())];

       return $this->where($map)->limit(0,1000)->select();

    }




}