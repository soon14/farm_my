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

class MattersModel extends Model
{

    /*
     * 新增理财
     */
    function addData($money){
        $back = $this->add([
            'user_id'=>session('user.id'),
            'cmc_price'=>CmcpriceController::getPrice(),
            'money'=>$money,
            'time'=>time(),
        ]);

        if ($back){
            return true;
        }else{
            $this->error('理财记录生成失败！');
            return false;
        }

    }

}