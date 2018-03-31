<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/15 0015
 * Time: 上午 10:46
 */

namespace Admin\Model;


use Think\Model;
use Think\Page;

class BonusListModel extends Model
{


    public $show;
    public $data;
    public $where_page;

    /**
     * 红包发放流水
     * @param $bonus_id 红包的id
     * @param $number 发放的数量
     * @param $that_revenue 税收金额
     * @param $repeats 发放的重销
     * @param $all_id 本次发放期数的id
     * @param $cmc_price cmc的单价
     * @return mixed
     */

    public function addList($bonus_id,$number,$that_revenue,$repeats,$all_id,$cmc_price){

        return $this->add([
                    'all_id'=>$all_id,
                    'number'=>$number,
                    'repeats'=>$repeats,
                    'bonus_id'=>$bonus_id,
                    'revenue'=>$that_revenue,
                    'time'=>time(),
                    'number_cmc'=>$number/$cmc_price,
                    'repeats_cmc'=>$repeats/$cmc_price,
                    'cmc_price'=>$cmc_price,
                    'revenue_cmc'=>$that_revenue/$cmc_price
                ]);
    }

    public function lists($where){

            $count  = $this ->where($where)
                    ->join('left join currency_bonus on currency_bonus.id = currency_bonus_list.all_id')
                    ->join('left join currency_users on currency_bonus.user_id = currency_users.id')
                    ->count();

            $Page = new Page($count,15);

            $this->show = $Page->show();

            $this->data = $this ->where($where)
                  ->field('currency_bonus_list.*,currency_users.users')
                  ->limit($Page->firstRow.','.$Page->listRows)
                  ->join('left join currency_bonus on currency_bonus.id = currency_bonus_list.bonus_id')
                  ->join('left join currency_users on currency_bonus.user_id = currency_users.id')
                  ->select();
            return $this;

    }


}