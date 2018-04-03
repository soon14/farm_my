<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/2
 * Time: 15:45
 */

namespace Home\Model;

use Common\Controller\CmcpriceController;
use Think\Model;
use Think\Page;

class MattersModel extends Model
{

    /*
     * 新增理财
     */
    function addData($money,$cfg){
        $back = $this->add([
            'user_id'=>session('user.id'),
            'cmc_price'=>CmcpriceController::getPrice(),
            'money'=>$money,
            'interest_cfg'=>$cfg['interest']/100,   //利率
            'designated'=>$cfg['time'],             //发放总期数
            'time_end'=>date("Y-m-d",strtotime("+30 day")),
            'time'=>time(),
        ]);

        if ($back){
            return true;
        }else{
            $this->error('理财记录生成失败！');
            return false;
        }

    }

    function getlist($where){
        $back = [];
        $wheres = ['user_id'=>session('user.id')];
        if (!empty($where)){
            array_push($wheres,$where);
        }

        $count =  $this->where($wheres)->count();
        $page = new Page($count,15,['time_start'=>I('time_start'),'time_end'=>I('time_end')]);

        $back['show'] = $page->show();

        $back['data'] = $this->where($wheres)->limit($page->firstRow,$page->listRows)->select();

        return $back;
    }


}