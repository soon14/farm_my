<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/31
 * Time: 10:08
 */

namespace Admin\Controller;
use Admin\Model\AngeInfoModel;
use Admin\Model\AngeModel;
use Admin\Model\AwardInfoModel;
use Admin\Model\AwardModel;
use Admin\Model\RepeatCfgModel;
use Admin\Model\UserpropertyModel;
use Think\Controller;
use Think\Exception;

/**
 * 奖金,转入金额 释放类
 * Class BalanceController
 * @package Admin\Controller
 */
class BonusaccountController extends Controller
{

    #发放配置项
    private $cnfg_award;

    private $cnfg_info;

    #本次发放的总金额
    private $awardall;

    private $infoAll;

    public function __construct()
    {
        parent::__construct();
//        if (empty(I('token')) || I('token')!=TOKEN){
//            exit(TOKEN);
//        }
        $RepeatCfgModel = new RepeatCfgModel();
        $this->cnfg_award = $RepeatCfgModel->getCfg('bonus_back');
        $this->cnfg_info = $RepeatCfgModel->getCfg('balance_back');
    }

    function snd(){

        $UserpropertyModel =  new UserpropertyModel();
        $UserpropertyModel_h = new \Home\Model\UserpropertyModel();
        $AwardModel = new AwardModel();
        $AwardInfoModel = new AwardInfoModel();

        $AngeModel = new AngeModel();
        $AngeInfoModel = new AngeInfoModel();

        $AwardModel->startTrans();
        try{
            #获取奖金本期释放的id
            $AwardModel->getNewAward();
            $AwardInfoModel->setAllId($AwardModel->getIdMy());

            #获取转入资产本期释放的id
            $AngeModel->getNewAnge();
            $AngeInfoModel->setAllId($AngeModel->getIdMy());

            $data = $UserpropertyModel->getPageList();
            if (empty($data)){

                return  $this->success('发放成功！（无可发放数据）');

            }

            foreach ($data as $v){

                #释放用户的奖金
                $this->awardall += $money_award = $v['award'] * $this->cnfg_award;
                if ($money_award>0){
                    $back = $AwardInfoModel->addInfo($v['id'],$money_award,$UserpropertyModel_h);
                    if ($back==false){
                        throw new Exception($AwardInfoModel->getError());
                    }
                }
                #释放用户转入资产
                $this->infoAll += $money_info = $v['cmc'] * $this->cnfg_info;
                if ($money_info>0){
                    $back = $AngeInfoModel->addInfo($v['id'],$money_award,$UserpropertyModel_h);
                    if ($back==false){
                        throw new Exception($AwardInfoModel->getError());
                    }
                }

                //如果有一个发放了，修改发放时间
                if ($money_award>0 || $money_info>0){
                    #修改用户的奖金释放时间
                    $back = $UserpropertyModel_h->where(['id'=>$v['id']])->save(['bonus_time'=>date('Y-m-d',time())]);
                    if (!$back){
                        $this->error='修改发放时间失败！错误信息：id=>'.$v['id'];
                        return false;
                    }
                }



            }

            #修改本次发放期数的总金额
            if ($this->awardall>0){
                $back = $AwardModel->saveMoney($this->awardall);
                if ($back==false){
                    throw new Exception($AwardModel->getError());
                }
            }

            if ($this->infoAll>0){
                $back = $AngeModel->saveMoney($this->awardall);
                if ($back==false){
                    throw new Exception($AngeModel->getError());
                }
            }


            $AwardModel->commit();
            return  $this->success('发放成功！');
        }catch (\Exception $e){

            $AwardModel->rollback();
            return $this->error($e->getMessage());
        }










    }




}