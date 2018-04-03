<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/31
 * Time: 14:09
 */

namespace Admin\Model;


use Think\Model;


/**
 * 奖金释放总金额
 * Class AwardModel
 * @package Admin\Model
 */
class AngeModel extends Model
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
    public function getNewAnge(){

        $data = $this->order('id desc')->find();

        if (date('Y-m-d',$data['time']) == date('Y-m-d',time())){

            $this->setIdMy($data['id']);

        }else{

            $back  = $this->add([
                'time'=>time()
            ]);
            $this->setIdMy($this->getLastInsID());

        }

        return $this;

    }

    public function saveMoney($money){

          $back =  $this->where(['id'=>$this->getIdMy()])->setInc('money',$money);
          if (!$back){
              $this->error='本期总金额修改失败！';
              return false;
          }

          return true;

    }

}