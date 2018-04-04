<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/4
 * Time: 16:25
 */

namespace Otc\Model;


use Think\Model;

class UserModel extends OtcModel
{



    public $user_my;

    /**
     * @return mixed
     */
    public function getUserMy()
    {
        return $this->user_my;
    }

    /**
     * @param mixed $user_my
     */
    public function setUserMy($user_my)
    {
        $this->user_my = $user_my;
    }



    function checkUsers(){

        $back = $this->where(['mobile'=>$this->user_my])->select();

        if (!empty($back)){
            return true;
        }

        return false;
    }
}