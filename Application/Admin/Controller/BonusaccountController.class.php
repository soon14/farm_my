<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/31
 * Time: 10:08
 */

namespace Admin\Controller;
use Admin\Model\AwardModel;
use Admin\Model\RepeatCfgModel;
use Admin\Model\UserpropertyModel;
use Think\Controller;

/**
 * 奖金释放类
 * Class BalanceController
 * @package Admin\Controller
 */
class Bonusaccount extends Controller
{

    #发放配置项
    private $cnfg;
    #本次发放的金额
    private $money_all;

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

        $AwardModel->startTrans();
        try{
            $AwardModel->getNewAward();

            if (empty($data)){

                $this->success('发放成功！（无可发放数据）');

            }

            $data = $UserpropertyModel->getPageList();
            foreach ($data as $v){

                $money = $v['award'] * $this->cnfg;

                $UserpropertyModel_h->setChangeMoney(C('award'),$money,$v['id'],'奖金释放',2);

            }



            $AwardModel->commit();
            return  $this->success('发放成功！');
        }catch (\Exception $e){

            $AwardModel->rollback();
            return $this->error($e->getMessage());
        }










    }




}