<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/3
 * Time: 17:03
 */

namespace Admin\Controller;


use Admin\Model\AreaModel;
use Think\Page;

class AreaController extends AdminController
{

    function index(){

        $AreaModel = new AreaModel();
        $count = $AreaModel->getCount()['count'];

        $page =  new Page($count,15);

        $show = $page->show();
        $this->assign('page',$show);

        $data =$AreaModel->limit($page->firstRow,$page->listRows)->select();
        $this->assign('data',$data);

        $this->display();
    }

    function add()
    {
        $areaModel = new AreaModel();
        $id = I('id');
        if (!empty($id)){
            $name=$areaModel->where(['id'=>$id])->field('name')->find();
            $this->assign('name',$name['name']);
        }
        $this->assign('id',$id);

        $this->display();
    }

    function Update(){
        $name= I('name');
        $id = I('id');
        if (empty($name)){
         return   $this->error('地区名称不能为空！',U('add'));
        }

        $areaModel = new AreaModel();
        if (empty($id)){
            $areaModel->add(['name'=>$name,'time'=>time()]);
            return  $this->success('添加成功！',U('index'));
        }else{
            $areaModel->where(['id'=>$id])->save(['name'=>$name,'time'=>time()]);
            return   $this->success('修改成功！',U('index'));
        }

    }

    function delete( )
    {

        $model= new AreaModel();
        $where = ['id'=>['in',I('ids')]];

        $msg = array('success' => '删除成功！', 'error' => '删除失败！');
        $model->where($where)->delete();
        $this->success('删除成功！',U('index'));

    }

}