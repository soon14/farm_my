<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/3
 * Time: 16:51
 */

namespace Admin\Controller;


use Admin\Model\MattersModel;
use Home\Model\UserpropertyModel;
use Think\Controller;
use Think\Exception;

class MatterssendController extends Controller
{


    function index(){

        $MattersModel =  new MattersModel();
        $data =$MattersModel->getlist();
        $UserpropertyModel = new UserpropertyModel();
        if (empty($data)){
            return    $this->success('无释放期数');
        }

        $MattersModel->startTrans();
        try{

            foreach ($data as $k=>$v){

                $money_old = round($v['money']* $v['cmc_price'],2)+$v['interest'];
                $money_old = round($money_old,2);
                $interest = round($money_old*interest_cfg,2);

                $back=$MattersModel->where(['id'=>$v['id']])->save([
                    'interest'=>round($v['interest']+$interest,2),
                    'designated_end'=>$v['designated_end']+1,
                    'time_end'=>date("Y-m-d",strtotime("+30 day"))
                ]);
                if (!$back){
                    throw new Exception('发放利息失败！错误信息=》ID=>'.$v['id']);
                }

                #如果到期了
                if ($v['designated_end']+1 == $v['designated']){

                    #释放用户的本金
                    $back = $UserpropertyModel->setChangeMoney(C('into'),round($v['interest']+$interest,2),$v['user_id'],'理财利息释放',2);
                    if (!$back){
                        throw new Exception('释放本金失败！错误信息ID->'.v['id'].'  '.$UserpropertyModel->getError());
                    }

                    #释放用户的利息
                    $back = $UserpropertyModel->setChangeMoney(C('balance'),$v['money'],$v['user_id'],'理财本金释放',2);

                    if (!$back){
                        throw new Exception('释放利息失败！错误信息ID->'.v['id'].'  '.$UserpropertyModel->getError());
                    }

                }
            }

            $MattersModel->commit();
            $this->success('发放成功！');
        }catch (\Exception $e){
            $MattersModel->rollback();
            $this->error('发放失败！');
        }



    }





}