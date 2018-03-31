<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/31
 * Time: 10:08
 */

namespace Admin\Controller;
use Admin\Model\AwardInfoModel;
use Admin\Model\AwardModel;
use Admin\Model\RepeatCfgModel;
use Admin\Model\UserpropertyModel;
use Think\Controller;
use Think\Exception;

/**
 * 奖金释放类
 * Class BalanceController
 * @package Admin\Controller
 */
class Bonusaccount extends Controller
{

    #发放配置项
    private $cnfg;

    #本次发放的总金额
    private $awardall;

    public function __construct()
    {
        parent::__construct();
//        if (empty(I('token')) || I('token')!=TOKEN){
//            exit(TOKEN);
//        }
        $RepeatCfgModel = new RepeatCfgModel();
        $this->cnfg = $RepeatCfgModel->getCfg('bonus_back');
    }

    function snd(){

        $UserpropertyModel =  new UserpropertyModel();
        $UserpropertyModel_h = new \Home\Model\UserpropertyModel();
        $AwardModel = new AwardModel();
        $AwardInfoModel = new AwardInfoModel();

        $AwardModel->startTrans();
        try{
            #获取本期释放的id
            $AwardModel->getNewAward();

            $AwardInfoModel->setAllId($AwardModel->getIdMy());
            if (empty($data)){

              return  $this->success('发放成功！（无可发放数据）');

            }

            $data = $UserpropertyModel->getPageList();
            foreach ($data as $v){

                #释放用户的奖金
                $this->awardall += $money = $v['award'] * $this->cnfg;

                $back = $AwardInfoModel->addInfo($v['id'],$money,$UserpropertyModel_h);
                if ($back==false){
                    throw new Exception($AwardInfoModel->getError());
                }

            }

            #修改本次发放期数的总金额
            $back = $AwardModel->saveMoney($this->awardall);
            if ($back==false){
                throw new Exception($AwardModel->getError());
            }

            $AwardModel->commit();
            return  $this->success('发放成功！');
        }catch (\Exception $e){

            $AwardModel->rollback();
            return $this->error($e->getMessage());
        }










    }




}