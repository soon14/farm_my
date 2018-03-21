<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/19
 * Time: 11:21
 */

namespace Home\Controller;


use Home\Model\BeforeModel;

class DatacheckController extends HomeController
{
    function data(){

        $Before = new BeforeModel();
        $data = $Before->getData();
        $this->assign('data',$data);
        $this->display();
    }

}