<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/3
 * Time: 16:58
 */

namespace Admin\Model;


use Think\Model;

class MattersModel extends Model
{
    /**
     * 用于发放时查询数据
     */
    function getlist(){

      return  $this->where('designated_end < designated  and time_end = "'.date("Y-m-d",time()).'"')->limit(0,1000)->select();

    }


}