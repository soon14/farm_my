<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/21 0021
 * Time: 下午 5:20
 */

namespace Common\Controller;

use Common\Model\UsersModel;
use Home\Model\UserpropertyModel;
use  MongoDB\BSON\toJSON;
use Think\Controller;
use Think\Exception;

class BonusController extends Controller
{

    private $user= [];

    private $cmc_price;

    private $register = 0;

    private $cfg=[];

    private $money;

    private $error;

    private $child_id;

    private $order;

    public function __construct()
    {
        parent::__construct();

        $CmcpriceController = new CmcpriceController();
        $this->cmc_price=$CmcpriceController->getPrice();
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param mixed $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }
    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param mixed $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }



    /**
     * @return mixed
     */
    public function getMoney()
    {
        return $this->money;
    }

    /**
     * @param mixed $money
     */
    public function setMoney($money)
    {
        $this->money = $money;
    }

    /**
     * @return array
     */
    public function getCfg()
    {
        return $this->cfg;
    }

    /**
     * @param array $cfg
     */
    public function setCfg($cfg)
    {
        $this->cfg = $cfg;
    }

    /**
     * @return int
     */
    public function getRegister()
    {
        return $this->register;
    }

    /**
     * @param int $register
     */
    public function setRegister($register)
    {
        $this->register = $register;
    }

    /**
     * @return array
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param array $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }



    public function getParent() {

        $bonus = M("bonus_distribution");

        $deduct = $bonus ->where(['key'=>'deduct'])-> find();

        $deduct = json_decode($deduct['data'],true);

        $subsidy = $bonus ->where(['key'=>'subsidy'])-> find();

        $subsidy = json_decode($subsidy['data'],true);

        #如果2个配置都为空，则不再进入流程
        if ( ( !is_array($deduct) || empty($deduct)) && (!is_array($subsidy) || empty($subsidy)) ){

            return true;
        }


        $this->setCfg(['deduct'=>$deduct,'subsidy'=>$subsidy]);

        $count = count($deduct);

        $UsersModel = new UsersModel();

        $UserpropertyModel = new UserpropertyModel();

        $UsersModel->startTrans();
        try{
            $this->child_id = $this->user['id'];

            $i = 0;
            #判断津贴是否已经发放
            $status = true;

            while (true){

                $i++;

                $Parent = $UsersModel->getPid($this->user['pid']);

                if(empty($Parent['id'])){
                    break;
                }

                $this->setUser($Parent);

                #直推人数
                $countChild = $UsersModel->countChild($this->user['id']);
                #团队人数
                $countChild_all = $Parent['team'];

                #红包提成,配置项必须正确才进入流程
                if (is_array($deduct) && !empty($deduct)){

                    if ($i<=$count){

                        $this->setRegister($i-1);

                        $back = $this->bonus($UserpropertyModel,$countChild);

                        if (!$back){
                            throw new Exception($this->getError());
                        }
                    }else{
                        break;
                    }

                }else{
                    break;
                }

                #判断管理津贴是否已经发放
                if ($status){
                    #红包津贴,配置项必须正确才进入流程
                    if (is_array($subsidy) && !empty($subsidy)){

                        $back = $this->subsidy($countChild,$UsersModel,$UserpropertyModel,$countChild_all,$status);

                        if (!$back){
                            throw new Exception($this->getError());
                        }
                    }
                }

                #如果父级的id为空 或者 管理津贴，红包提成都已发放则退出循环
                if ( empty($Parent['pid']) || ($status==false&&$i>=$count) ){
                    break;
                }

            }

            $UsersModel->commit();

            return true;

        }catch (\Exception $e){
            $UsersModel->rollback();
            return false;
        }



    }

    /**
     * 红包提成
     * @param UserpropertyModel $userpropertyModel
     * @param $countChild 直推人数
     * @param $countChild_all 团队人数
     * @return bool
     */
    public function bonus(UserpropertyModel $userpropertyModel ,$countChild ){

        $numpeople = $this->cfg['deduct'][$this->register]['numpeople'];

        $percentage = $this->cfg['deduct'][$this->register]['percentage'];

        #如果满足条件,红包提成

        if ($countChild>=$numpeople){

           $money = $this->money * $percentage/100;

           $back = $userpropertyModel->setChangeMoney(5,$money/$this->cmc_price,$this->user['id'],'红包分成',2);

           if (!$back){
               $this->error = $userpropertyModel->getError();
               return false;
           }

           #红包分层记录
            $back = M('bonus_deduct')->add([
                'user_id'=>$this->user['id'],
                'child_id'=>$this->child_id,
                'number'=>$money,
                'number_cmc'=>$money/$this->cmc_price,
                'order'=>$this->order,
                'type'=>1,
                'time'=>time()
            ]);

           if (!$back){

               $this->error='提成记录生成失败！';

               return false;
           }

        }

        return true;

    }


    /**
     * 红包津贴
     * @param $countChild  直推人数
     * @param $countChild_all 团人数
     * @param $status 判断是否满足条件，用于控制是否再次调用
     * @param UsersModel $usersModel
     * @param UserpropertyModel $userpropertyModel
     * @return bool
     */
    public function subsidy($countChild,UsersModel $usersModel,UserpropertyModel $userpropertyModel,$countChild_all,&$status){

        $number = 0;

        #获取团队人数

        /* 弃用该方法
        $usersModel->countChild_all($this->user['id'],$number);
        */
        $this->countChild_all($countChild_all);

        $percentage = $this->getNumpeople_all($countChild,$countChild_all);

        if ( $percentage){
            #满足条件修改 状态为false 下一轮将不再发放管理津贴
            $status = false;

            $money = $this->money*$percentage/100;
            $back = $userpropertyModel->setChangeMoney(C('award'),$money,$this->user['id'],'管理津贴',2);

            if (!$back){
                $this->error = $userpropertyModel->getError();
                return false;
            }

            #红包津贴记录
            $back = M('bonus_deduct')->add([
                'user_id'=>$this->user['id'],
                'child_id'=>$this->child_id,
                'number'=>$money,
                'order'=>$this->order,
                'type'=>2,
                'time'=>time()
            ]);

            if (!$back){

                $this->error='提成记录生成失败！';

                return false;
            }

        }
        return true;
    }


    /**
     * 判断是否满足发放管理津贴的条件
     * @param $count 直推人数
     * @param $child_all 团队人数
     * @return bool
     */
    public function getNumpeople_all($count,$child_all){

        foreach($this->cfg['subsidy'] as $k=>$v){

            #判断是否满足直推人数
            if ($count >= $v['numpeople']){

                #判断是否满足团队人数
                if ($child_all >= $v['numpeople_all']){

                    #返回津贴比例
                    return $v['percentage'];

                }

            }

        }

        return false;

    }



}