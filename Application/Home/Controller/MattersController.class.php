<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/2
 * Time: 15:17
 */

namespace Home\Controller;


use Common\Controller\CmcpriceController;
use Home\Model\MattersModel;
use Home\Model\UserpropertyModel;
use Think\Exception;

class MattersController extends HomeController
{

    /*
     * 理财功能的实现
     */
    function index(){
        $matters_cfg_m = M('matters_cfg');
        $matters_m = new MattersModel();
        $userpropertyModel = new UserpropertyModel();
        $matters_m->startTrans();
        try{

            $money = I('money');
            $money_type = I('money_type');
            $type =I('type');

            //验证参数
            if ($money_type != 'into' && $money_type != 'balance'){
                return $this->error('非法账户');
            }

            $cfg= $matters_cfg_m->where(['id'=>$type])->find();

            if (empty($cfg['id'])){
                return $this->error('非法类型');
            }

            #理财记录生成

            $back = $matters_m->addData($money,$cfg);
            if (!$back){
                throw new Exception($matters_m->getError());
            }

            #扣除用户金额
            $back =$userpropertyModel->setChangeMoney(C($money_type),$money,session('user.id'),'转入理财',1);
            if (!$back){
                throw new Exception($userpropertyModel->getError());
            }

            $matters_m->commit();

            $this->success('理财成功！');
        }catch (\Exception $e){

            $matters_m->rollback();
            $this->error('理财失败！'.$e->getMessage());
        }




    }

    /**
     * 理财页面
     */

    function view(){
        $userproperty_m = M('userproperty');

        $user_data = $userproperty_m->where(['userid'=>session('user.id')])->find();
        $matters_cfg_m = M('matters_cfg');

        $cnfg=$matters_cfg_m->select();

        $this->assign('cnfg',$cnfg);
        $this->assign('user_data',$user_data);
        $this->display();
    }


    /**
     * 用户的理财记录
     */

    function Matters_list(){

        $where= [];
        $time_start = I('time_start');
        $time_end = I('time_end');

        if (!empty($time_start) && $time_end){
            $where = ['time'=>[['egt',strtotime($time_start)],['elt',strtotime($time_end)+86400],'and']];
        }

        $MattersModel = new MattersModel();

        $data = $MattersModel->getlist($where);

        $this->assign('page',$data['show']);
        $this->assign('data',$data['data']);
        $this->display();
    }



}