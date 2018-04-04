<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/13 0013
 * Time: 下午 3:38
 */

namespace Home\Model;


use Think\Model;

class UsersModel extends Model
{

    function getUserData($id,$field=',users'){

        return $this->where(['id'=>$id])->field('id,'.$field)->find();

    }

    function is_child($user){

        $back =$users = M('users')->field('id,username,pid')->where(['users'=>$user])->find();

        while (true){

            $data = M('users')->field('id,username,pid')->where(['id'=>$users['pid']])->find();

            if (empty($data['id'])){
                $this->error=('接收人不存在！');
                return false;
                break;
            }

            if ($data['id'] == session('user.id')){
                $data['username'] = $data['username'] ? $data['username']:'**';

                return $back;
                break;
            }

            if (empty($data['pid'])){
                 $this->error=('接收人不存在！');
                 return false;
                 break;
            }

            $users = $data;

        }
    }



    function team(array $user){

        while (true){

            $prend =  $this->field('pid,id')->where(['id'=>$user['pid']])->find();

            if (empty($prend)){
                break;
            }

            $this->where(['id'=>$prend['id']])->setInc('team',1);

            if (empty($prend['pid']) || $prend['pid']==0){
                break;
            }

            $user = $prend;

        }

        return true;

    }


}