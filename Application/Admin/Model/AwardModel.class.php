<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/31
 * Time: 14:09
 */

namespace Admin\Model;


use Think\Model;

class AwardModel extends Model
{

    private $id_my;

    private $Award_all=0;

    /**
     * @return mixed
     */
    public function getIdMy()
    {
        return $this->id_my;
    }

    /**
     * @param mixed $id_my
     */
    public function setIdMy($id_my)
    {
        $this->id_my = $id_my;
    }


    /**
     * 获取一个释放期数id
     * @return $this
     */
    public function getNewAward(){

        $data = $this->order('id desc')->find();

        if (date('Y-m-d',$data['time']) == date('Y-m-d',time())){

            $this->setIdMy($data['id']);

        }else{

            $back  = $this->add([
                'time'=>time()
            ]);
            $this->getIdMy($this->getLastInsID());

        }

        return $this;

    }

    public function saveMoney($money){

          $back =  $this->where(['id'=>$this->id_my])->setInc('money',$money);
          if (!$back){
              $this->error='本期总金额修改失败！';
              return false;
          }

          return true;

    }

}