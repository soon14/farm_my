<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/4
 * Time: 16:25
 */

namespace Otc\Model;
use Otc\Model\OtcModel;

class UserModel extends OtcModel
{
    private $user;

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    function checkUser(){

        $back = $this->where(['mobile'=>$this->user])->find();

        if (!empty($back)){
            return true;
        }

        return false;

    }
}