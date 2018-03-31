<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/31
 * Time: 14:09
 */

namespace Admin\Model;


use Think\Model;

class AwardInfoModel extends Model
{
    /**
     * 释放期数的id
     * @var
     */
    private $all_id;

    /**
     * @return mixed
     */
    public function getAllId()
    {
        return $this->all_id;
    }

    /**
     * @param mixed $all_id
     */
    public function setAllId($all_id)
    {
        $this->all_id = $all_id;
    }

    /**
     * 释放奖金到余额，并且生成释放记录
     * @param $user_id
     * @param $money
     */
    public function  addInfo($user_id,$money,\Home\Model\UserpropertyModel $userpropertyModel){

            #减少用户的奖金账户
            $back = $userpropertyModel->setChangeMoney(C(''),$money,$user_id,'奖金释放',1);
            if (!$back){
                $this->error='奖金账户扣除失败！错误信息：id=>'.$user_id.'money=>'.$money;
                return false;
            }

            #增加用户的余额账户
            $back = $userpropertyModel->setChangeMoney(C(''),$money,$user_id,'奖金释放',2);
            if (!$back){
                $this->error='余额账户增加失败！错误信息：id=>'.$user_id.'money=>'.$money;
                return false;
            }

            #修改用户的奖金释放时间
            $back = $userpropertyModel->where(['id'=>$user_id])->save(['bonus_time'=>date('Y-m-d',time())]);
            if (!$back){
                $this->error='修改发放时间失败！错误信息：id=>'.$user_id;
                return false;
            }

            #生成释放记录
            $back = $this->add([
                'all_id'=>$this->all_id,
                'user_id'=>$user_id,
                'time'=>time(),
                'money'=>$money
            ]);
            if (!$back){
                $this->error='释放记录生成失败！错误信息：id=>'.$user_id;
                return false;
            }

            return true;
    }

}