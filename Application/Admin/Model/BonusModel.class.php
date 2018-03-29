<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/13 0013
 * Time: 下午 5:36
 */

namespace Admin\Model;


use Think\Model;
use Think\Page;

class BonusModel extends Model
{

    public $data;

    public $show;

    public $page_where;

    /**
     * 获取总条数
     * @param $where
     * @return mixed
     */
    public function getCount($where){

       return $this->where($where)->count();

    }


    /**
     * 分页获取数据
     * @param $where
     * @param $page
     * @param int $number_this
     * @return mixed
     */

    public function getDataPage($where,$page,$number_this=0){

//        $page+=1;
//
//        $number_this = $number_this == 0 ? C('COUNT') : $number_this;
//
//        $start = ($page-1)*$number_this;
//
//        $end   = $number_this;

//        return $this->where($where)->limit($start,$end)->select();
        return $this->where($where)->limit(0,1000)->select();

    }


    /**
     * 发放功能,生成发放记录，并且添加已发放金额
     * @param $id 红包的id
     * @param $number  发放的数量
     * @param $that_revenue 税收金额
     * @param $repeats 发放的重销
     * @param $all_id 本次发放期数的id
     * @param $provide 已经发放的总数（850）
     * @param $cmc_price cmc的单价
     * @return bool|mixed
     */
    public function saveData($id,$number,$that_revenue,$repeats,$all_id,$provide,$cmc_price){

        $back = $this->where(['id'=>$id])->save(['provide'=>$number+$repeats+$provide+$that_revenue,'send_time'=>date('Y-m-d',time())]);
        if (!$back){
            return false;
        }

        $bonusListModel = new BonusListModel();

        return $bonusListModel->addList($id,$number,$that_revenue,$repeats,$all_id,$cmc_price);

    }




    public function lists($where){

        $count =  $this->where($where)
                        ->field('currency_bonus.*,currency_users.users')
                        ->join('left join currency_users on  currency_bonus.user_id = currency_users.id')
                        ->count();

        $Page = new Page($count,15,$this->page_where);

        $this->show = $Page->show();

        $this->data = $this->where($where)
                           ->limit($Page->firstRow.','.$Page->listRows)
                           ->field('currency_bonus.*,currency_users.users')
                            ->order('currency_bonus.time desc')
                           ->join('left join currency_users on  currency_bonus.user_id = currency_users.id')->select();

        return $this;
    }





}