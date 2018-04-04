<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/4
 * Time: 17:45
 */

namespace Home\Controller;


use Home\Model\UserpropertyModel;
use Home\Model\UsersModel;
use Otc\Model\UserCoinModel;
use Otc\Model\UserModel;
use Think\Exception;

class ShiftController extends HomeController
{
    /**
     * 资产转移发放
     */

    function index(){

        $UserpropertyModel =  new UserpropertyModel();
        $user_property = $UserpropertyModel->where(['id'=>session('user.id')])->find();
        if ($user_property['cny']<=0){
            return $this->success('无资产可转移','',true);
        }
        $mobile = session('user.user_name');

        $UsersModel_f =  new UsersModel();
        $user_data = $UsersModel_f->where(['id'=>session('user.id')])->find();

        /**
         * 查看otc平台是否已存在用户
         */
        $UserModel_o = new UserModel();
        $user_o = $UserModel_o->where(['mobile'=>$mobile])->find();
        $UserCoinModel = new UserCoinModel();
        $UserModel_o->startTrans();

            try{

                if (empty($user_o)){  //如果不存在 ，就添加用户，并生产用户资产表
                        $u_id = $UserModel_o->add([
                            'username'=>$mobile,
                            'mobile'=>$mobile,
                            'enname'=>$mobile,
                            'password'=>$user_data['password'],
                            'status'=>1
                        ]);

                        if (!$u_id){
                            throw new Exception('用户生产失败！','',true);
                        }

                        $back = $UserCoinModel->add([
                            'userid'=>$u_id
                        ]);
                        if (!$back){
                            throw new Exception('用户资产生产失败！');
                        }
                    $user_o['id'] = $u_id;
                }
                    #扣除用户cny
                    $back = $UserpropertyModel->setChangeMoney(C('balance'),$user_property['cny'],session('user.id'),'资产转移',1);
                    if (!$back){
                        throw new Exception($UserpropertyModel->getError());
                    }

                    #增加otc用户的金额

                    $back = $UserCoinModel->where(['userid'=>24])->setInc('cmc',$user_property['cny']);

                    if (!$back){
                        throw new Exception('用户资产转移失败！');
                    }

                    $UserModel_o->commit();

                    $this->success('转移成功！','',true);
            }catch (\Exception $exception){

                $UserModel_o->rollback();
                return $this->success($exception->getMessage(),'',true);

            }







    }




}