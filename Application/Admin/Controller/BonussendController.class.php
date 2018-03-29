<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/23
 * Time: 10:07
 */

namespace Admin\Controller;
use Admin\Model\BonusAllModel;
use Admin\Model\BonusModel;

use Admin\Model\RepeatCfgModel;

use Common\Model\UsersModel;
use Home\Model\UserpropertyModel;
use  MongoDB\BSON\toJSON;
use Think\Controller;
use Think\Exception;
use Think\Model;
use Common\Controller\CmcpriceController;

class BonussendController extends Controller
{
    public $cmc_price;

    public function __construct()
    {
        parent::__construct();
//        if (empty(I('token')) || I('token')!=TOKEN){
//            exit(TOKEN);
//        }
        $CmcpriceController = new CmcpriceController();
        $this->cmc_price=$CmcpriceController->getPrice();
    }

    #红包的发放
    public function  bonus(){

        $bonus_m  =  new BonusModel();

        $repeatCfgModel = new RepeatCfgModel();

        $usersModel = new UsersModel();

        #日返率
//        $date_back = array_sort($repeatCfgModel->getCfg('date_back'));
        $date_back = $repeatCfgModel->getCfg('date_back');

        #红包的重消配置
        $repeat_paper = $repeatCfgModel->getCfg('repeat_paper');

        #红包扣税率
        $revenue = $repeatCfgModel->getCfg('revenue');

        #本次cny放数总发
        $all_money = 0;

        #本次重消放数总发
        $all_repeat = 0;

        $where = '(provide < outs) and send_time!="'.date('Y-m-d',time()).'"';

        $count = $bonus_m->getCount($where);

        #没有数据的情况
        if (!$count){
            return false;
        }

        $bonus_m->startTrans();
        try{

            $bonusAllModel =  new BonusAllModel();
            $nullBonusAll_id=0;
            $nullBonusAll_data = $bonusAllModel->where(['end_time'=>date('Y-m-d',time())])->find();

           if (empty($nullBonusAll_data['id'])){
               $nullBonusAll = $bonusAllModel->addNullBonusAll();
               if (!$nullBonusAll){
                   throw new Exception('发放失败！');
               }
               $nullBonusAll_id = $bonusAllModel->getLastInsID();
           }else{
               $nullBonusAll_id = $nullBonusAll_data['id'];
           }

            #返回用户金额
            $userproperty = new UserpropertyModel();  //home 模块的 Userproperty
//            for ($i = 0; $i<$count/C('Count');$i++){

                $data = $bonus_m->getDataPage($where,1);

                foreach ($data as $k=>$v){

//                    $child_number = 0;

//                    $usersModel->countChild_all($v['user_id'],$child_number);

                    #应返金额(购买数量*日返金额)
                    $money = $v['number']*$date_back;

                    #判断出局金额与应返金额的关系

                    $money = $money+$v['provide'] <= $v['outs'] ? $money : $v['outs']-$v['provide'];

                    #扣除税收
                    $that_revenue = ($money*$revenue);

                    $money = $money - $that_revenue;

                    #红包的重消金额
                    $all_repeat +=$repeat_money = $money*$repeat_paper;

                    #发放的cny
                    $money = $money - $repeat_money;

                    $all_money += $money;


                    #发放用户cny
                    $back = $userproperty->setChangeMoney(1,$money/$this->cmc_price,$v['user_id'],'红包分红',2);

                    if (!$back){
                        throw new Exception($userproperty->getError());
                    }


                    #发放用户重消
                    $back = $userproperty->setChangeMoney(3,$repeat_money/$this->cmc_price,$v['user_id'],'红包分红',2);
                    if (!$back){
                        throw new Exception($userproperty->getError());
                    }

                    #生成发放流水，并且修改本次已发放金额
                    $back = $bonus_m->saveData($v['id'],$money,$that_revenue,$repeat_money,$nullBonusAll_id,$v['provide'],$this->cmc_price);
                    if (!$back){
                        throw new Exception($userproperty->getError());
                    }

                }


//            }

            #成功后修改本次发放总数
            $back = $bonusAllModel->saveNullBonusAll($nullBonusAll_id,$all_money+$nullBonusAll_data['number'],$all_repeat+$nullBonusAll_data['repeats'],$this->cmc_price);
            if ($back===false){

                throw new Exception('发放失败！');

            }

            $this->success('红包发放成功！');
            $bonus_m->commit();

        }catch (\Exception $e){

            $this->error($e->getMessage());

            $bonus_m->rollback();
        }

    }

    public function cssql(){
        $a = new Model('addcs');
        $a->addAll([['id'=>1,'key'=>'122','value'=>3]],[],true);

    }


}