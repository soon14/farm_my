<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/3
 * Time: 15:20
 */

namespace Admin\Controller;


use Think\Page;

class MattersController extends AdminController
{

    /**
     * 理财记录
     */
    function index(){
        $matters_m = M('matters');

        $count = $matters_m->where()->count();
        $page = new Page($count,15);
        $data= $matters_m->where()->limit($page->firstRow,$page->listRows)
            ->field('currency_users.users,currency_matters.*')
            ->join(' left join currency_users on currency_matters.user_id = currency_users.id')->select();

        $this->assign('data',$data);
        $this->assign('page',$page->show());

        $this->display();
    }

    /**
     * 奖金释放
     */
    function award(){
        $award_m = M('award');

        $count = $award_m->where()->count();
        $page = new Page($count,15);

        $data = $award_m->where()->limit($page->firstRow,$page->listRows)->select();

        $this->assign('data',$data);
        $this->assign('page',$page->show());

        $this->display();
    }

    function award_info(){
        $award_m = M('award_info');
        $where= ['all_id'=>I('id')];

        $count = $award_m->where($where)->count();
        $page = new Page($count,15);

        $data = $award_m->where($where)
            ->field('currency_users.users,currency_award_info.*')
            ->join(' left join currency_users on currency_award_info.user_id = currency_users.id')
            ->limit($page->firstRow,$page->listRows)->select();

        $this->assign('data',$data);
        $this->assign('page',$page->show());
        $this->display();
    }

    /**
     * 余额释放
     */

    function enter(){
        $award_m = M('ange');

        $count = $award_m->where()->count();
        $page = new Page($count,15);

        $data = $award_m->where()->limit($page->firstRow,$page->listRows)->select();

        $this->assign('data',$data);
        $this->assign('page',$page->show());

        $this->display();


        $this->display();
    }

    function enter_info(){
        $award_m = M('ange_info');
        $where= ['all_id'=>I('id')];

        $count = $award_m->where($where)->count();
        $page = new Page($count,15);

        $data = $award_m->where($where)
            ->field('currency_users.users,currency_ange_info.*')
            ->join(' left join currency_users on currency_ange_info.user_id = currency_users.id')
            ->limit($page->firstRow,$page->listRows)->select();

        $this->assign('data',$data);
        $this->assign('page',$page->show());

        $this->display();
    }


}