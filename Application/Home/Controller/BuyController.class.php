<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use Common\Model\UsersModel;
use Home\Model\UserpropertyModel;
use Home\Model\IntegralModel;
use Common\Controller\CmcpriceController;
use OT\DataDictionary;
use Think\Page;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class BuyController extends HomeController {
    private $cat_id = 1;
    private $search = "";
    private $Page;

    public function __construct() {
        parent::__construct();

        if (session('user')['id']==""){
            $this->redirect('Index/index');
            exit();
        }

        $cate = $this -> gettree();

        $this -> assign("cate", $cate);
    }

	//购买过
    public function buy(){
        //将产品信息提交到确认订单页面
        $product_id = $this -> strFilter(I("product_id")) ? I("product_id") : null;
        if ($product_id != null) {
            $product_info = M()
                -> table("currency_product as p")
                -> field("p.id, p.name, p.img, p.price, pc.type,p.out")
                -> join("left join currency_procate as pc on p.cat_id = pc.id")
                -> where("p.id = ". $product_id)
                -> find();
            $product_info['price_show'] = $this -> getPriceShow($product_info['type'], $product_info['price'], $product_info['id']);
            if ($product_info['out']>0) {
                $cmcpriceController = new CmcpriceController();
                $cmc_price = $cmcpriceController->getPrice();
                $product_info['price_show'] = $product_info['price_show']/$cmc_price;
                $product_info['price'] = $product_info['price']/$cmc_price;
            }


        }


        //查询是否有默认地址，没有则选择地址
        $default = M()
            -> table("currency_shop_address as sa")
            -> field("sa.id, sa.name, sa.mobile, sa.address, sc.city_name as province, scc.city_name as city , sccc.city_name as area, sa.status")
            -> join("left join currency_shop_city as sc on sc.id = sa.province")
            -> join("left join currency_shop_city as scc on scc.id = sa.city")
            -> join("left join currency_shop_city as sccc on sccc.id = sa.area")
            -> where("sa.user_id = ". session('user')['id'])
            -> order("sa.status")
            -> select();

        $this -> assign("info", $product_info);
        $this -> assign("default", $default);
        $this -> display();
    }

    //获取收货地址


    //加入购物车
    public function cart() {
        
    }

    //结算确认订单
    public function confirm() {
        //不同类别的产品不能同时结算
        //不同类别的产品结算方式不同
        //生成订单
        $product_id = $this -> strFilter(I("product_id")) ? I("product_id") : null;
        $type = $this -> strFilter(I("type")) ? I("type") : null;
        $number = $this -> strFilter(I("number")) ? I("number") : null;
        $ship_id = $this -> strFilter(I("ship_id")) ? I("ship_id") : null;
        // if ($ship_id == "" || $ship_id == null || $ship_id == "undefined") {
        //     $this -> error("请选择收货地址");
        //     exit();
        // }
        
        $product_info = M()
            -> table("currency_product as p")
            -> field("p.id, p.name, p.img, p.price, pc.type,p.out")
            -> join("left join currency_procate as pc on p.cat_id = pc.id")
            -> where("p.id = ". $product_id)
            -> find();
        $product_info['price_single'] = $this -> getPriceShow($product_info['type'], $product_info['price'], $product_info['id']);
        $product_info['price_show'] = $this -> getPriceShow($product_info['type'], $product_info['price'], $product_info['id'], $number);
        if ($product_info['out']>0) {
            $cmcpriceController = new CmcpriceController();
            $cmc_price = $cmcpriceController->getPrice();
            $product_info['price_single'] = $product_info['price_single']/$cmc_price;
            $product_info['price_show'] = $product_info['price_show']/$cmc_price;
            $product_info['price'] = $product_info['price']/$cmc_price;
        }



        //支付方式 method 1余额 2CMC+人民币 3红包重消  4积分
        switch ($type) {
            case '1':
                $method = array(array("method" => 1, "name" => "cmc余额"), array("method" => 3, "name" => "红包重消账户"));
                break;
            case '2':
                $method = array(array("method" => 2, "name" => "确认支付"));
                break;
            case '3':
                $method = array(array("method" => 1, "name" => "余额账户"), array("method" => 4, "name" => "积分"), array("method" => 3, "name" => "红包重消账户"));
                break;
            default:
                
                break;
        }
        // var_dump($method);
        $this -> assign("method", $method);
        $this -> assign("number", $number);
        $this -> assign("total_price", $number * $product_info['price']);
        $this -> assign("ship_id", $ship_id);
        $this -> assign("info", $product_info);
        $this -> display();
    }

    //支付
    public function pay() {
        //判断交易密码是否正确
        //生成订单号
        //插入订单表
        //用户资产明细表
        //根据类型扣除相应表的资金

        $data['product_id']    = $this -> strFilter(I("product_id")) ? I("product_id") : null;

        $info = M("product as p")   
            -> field("p.name, p.img, p.price, pc.type") 
            -> join("currency_procate as pc on pc.id = p.cat_id")
            -> where("p.id = ". $data['product_id']) 
            -> find();

        $data['product_name']  = $info['name'];
        $data['product_img']   = $info['img'];
        $data['product_price'] = $info['price'];
        $data['product_type']  = $info['type'];        //所属商城
        $data['number']        = $this -> strFilter(I("number")) ? I("number") : null;
        $data['total_money']   = I("total_money") ? I("total_money") : null;
        $data['ship_id']       = $this -> strFilter(I("ship_id")) ? I("ship_id") : null;
        $data['user_id']       = session("user")['id'];
        $data['status']        = 1;
        $data['time']          = time();
        $data['order']         = date("Ymd", time()).session("user")['id'].rand(100,999);

        // $deal_pwd              = I("deal_pwd") ? I("deal_pwd") : null;
        $method                = $this -> strFilter(I("method")) ? I("method") : null;
        $price                 = I("price") ? I("price") : null;

        $data['method']        = $method;

        // if ($deal_pwd == null) {
        //     $this -> error("交易密码不能为空");
        //     exit();
        // } else {
        //     if (session('user')['dealpwd']!=jiami($deal_pwd)){   //交易密码验证
        //         $this->error('交易密码不正确！');
        //         exit();
        //     }
        // }

        //判断红包商城，购买次数
        if ($data['product_type'] == 1) {
            $time = strtotime(date("Y-m-d", time()));
            $count = M("shop_order")
                -> field("sum(number) as count")
                -> where("time >= ". $time. " AND user_id = ". session("user")['id']. " AND method = ". $method. " AND product_type = ". $data['product_type'])
                -> find();

            $count_all = M("shop_order")
                -> field("sum(number) as count")
                -> where(" user_id = ". session("user")['id']. " AND method = ". $method. " AND product_type = ". $data['product_type'])
                -> find();
            if ($method == 1) {
                $method_cn = "余额支付";
                $key = "cny";
            }

            if ($method == 3) {
                $method_cn = "红包重消支付";
                $key = "repeat";
            }

            $cfg = M("shop_cfg")
                -> field("data")
                -> where(['key' => $key])
                -> find();
            if ($count['count'] != NULL) {

                if ($count_all['count']>100){
                    return $this -> error("您已超过最大红包限购量100");
                }

                if ($count['count'] >= $cfg['data']) {
                    $this -> error("今日购买红包次数已用完");
                    exit();
                } else {
                    if ($data['number'] > ($cfg['data'] - $count['count'])) {
                        $this -> error("今日可用".$method_cn."方式购买".($cfg['data'] - $count['count'])."个");
                        exit();
                    }
                }
            } else {
                if ($data['number'] > $cfg['data']) {
                    $count['count'] = 0;
                    $this -> error("今日可用".$method_cn."方式购买".($cfg['data'] - $count['count'])."个");
                    exit();
                }
            }
            
            
        }

        if ($method != null) {
            $user_proper = new UserpropertyModel();
            $user_proper -> startTrans();

            //扣钱
            switch ($method) {
                case '1': //余额
                    $res1 = $user_proper -> setChangeMoney(1, $data['total_money'], session("user")['id'], "购买红包", 1);
                    if ($res1 > 1) {
                        $res_ins = M("shop_order") -> add($data);

                        if ($res_ins) {

                            if ($data['product_type'] == 1){

                                $this->release(1,$res_ins);
                            }

                            $user_proper -> commit();
                            $this -> success("购买成功");
                        } else {
                            $user_proper -> rollback();
                            $this -> error("购买失败");
                        }
                    } else {
                        $this -> error($user_proper -> getError());
                    }
                    break;
                case '2': //CMC+余额
                    // CMC查询id
                    $cmc_id = M("xnb") -> field("id") -> where("brief = 'CMC'") -> find();
                    //查询所需要的CMC数量
                    $price_need = $this -> getPriceShow(2, $price, $data['product_id'], $data['number'] );
                    
                    $res2_1 = $user_proper -> setChangeMoney($cmc_id['id'], $price_need['cmc'], session("user")['id'], "报单", 1);
                    
                    if ($res2_1 > 1) {
                        //扣除人民币数量
                        $res2_2 = $user_proper -> setChangeMoney(1, $price_need['cny'], session("user")['id'], "报单", 1);
                        if ($res2_2 > 1) {
                            $cmc = new CmcpriceController();
                            $cmc_price = $cmc -> getPrice();
                            $data['cmc'] = $cmc_price;

                            $res_ins = M("shop_order") -> add($data);

                            if ($res_ins) {
                                $user_proper -> commit();
                                $this -> success("购买成功");
                            } else {
                                $user_proper -> rollback();
                                $this -> error("购买失败");
                            }
                        } else {
                            $this -> error($user_proper -> getError());
                        }
                    } else {
                        $this -> error($user_proper -> getError());
                    }
                    break;
                case '3': //红包重消
                    $res3 = $user_proper -> setChangeMoney(3, $data['total_money'], session("user")['id'], "购买商品", 1);
                    if ($res3 > 1) {
                        
                            $res_ins = M("shop_order") -> add($data);

                            if ($res_ins) {

                                if ($data['product_type'] == 1){

                                    $this->release(1,$res_ins);
                                }



                                $user_proper -> commit();
                                $this -> success("购买成功");
                            } else {
                                $user_proper -> rollback();
                                $this -> error("购买失败");
                            }
                        
                    } else {
                        $this -> error($user_proper -> getError());
                    }
                    break;
                case '4': //积分 价格/cmc价格
                    $cmc = new CmcpriceController();
                    $cmc_price = $cmc -> getPrice();
                    $price = $price = round($data['total_money'] / $cmc_price, 2);;
                    $inte = new IntegralModel(session("user")['id'], $price);

                    $all_oldintegral = $inte -> getAllIntegral(session("user")['id']);
                    // var_dump($all_oldintegral);

                    $res4 = $inte -> lessintgral(session("user")['id'], $price);
                    // var_dump($price);

                    if ($res4 == "积分不足") {
                        $this -> error("积分不足");
                    } else {
                        if ($res4) {
                            $res5 = $user_proper -> detail(['id' => session("user")['id'], "name" => ''], 0, (-1 * $price), "购买商品", $all_oldintegral);
                            $res_ins = M("shop_order") -> add($data);

                            if ($res_ins) {
                                $user_proper -> commit();
                                $this -> success("购买成功");
                            } else {
                                $user_proper -> rollback();
                                $this -> error("购买失败");
                            }
                        } else {
                            $this -> error("购买失败");
                        }
                    }
                    break;
                default:
                    break;
            }

        } else {
            $this -> error("请选择支付方式");
            exit();
        }
    }

    //确认收货
    public function receiving() {
        $id = I("order_id");
        $res = M("shop_order") -> save(['id' => $id, 'status' => 3]);
        if ($res) {
            $this -> success("确认收货成功");
        } else {
            $this -> error("确认收货失败");
        }
    }

    //改变购买数量，计算价格
    public function getPrice() {
        $type       = I("type") ? I("type") : "";
        $price      = I("price") ? I("price") : "";
        $product_id = I("product_id") ? I("product_id") : "";
        $number     = I("number") ? I("number") : 0;

        if ($number == 0) {
            $this -> error("数量不能为0");
            exit();
        }

        $price_show = $this -> getPriceShow($type, $price, $product_id, $number);



        if (is_array($price_show)) {
            $price_show = $price_show['cmc'] . " CMC " . " + " . $price_show['cny'] . " CNY " ;
        }
        $this -> success($price_show);
    }

    /*文章类型*/
    public function gettree($id = 0, $field = true){
        /* 获取当前分类信息 */
        $table=M('procate');
        if($id){
            $info = $table->info($id);
            $id   = $info['id'];
        }

        /* 获取所有分类 */
        $map  = array('status' => array('eq', 1));
        $list = $table->field($field)->where($map)->select();
        $list = list_to_tree($list, $pk = 'id', $pid = 'pid', $child = 'child', $root = $id);

        /* 获取返回数据 */
        if(isset($info)){ //指定分类则返回当前分类极其子分类
            $info['child'] = $list;
        } else { //否则返回所有分类
            $info = $list;
        }

        return $info;
    }

    private function getPriceShow($type, $price, $id, $number = 1) {
        if ($number == 1) {
            switch ($type) {
                case 1: //红包 展示需要多少人民币
                    $price_show = $price;
                    break;
                case 2: //报单 展示需要多少CMC和人民币
                    //获取后台配置的CMC当前价格及报单属性
                    $cfg = new CmcpriceController();
                    $cmc_price = $cfg -> getPrice();
                    $attr = M("product") 
                        -> field("cmc, cny")
                        -> where("id = ". $id)
                        -> find();
                    // var_dump($attr);

                    $price_show = array("cmc" => $attr['cmc'], "cny" => $attr['cny'] * $cmc_price);
                    break;
                case 3: //重消 展示需要多少人民币
                    $price_show = $price;
                    break;
                default:
                    # code...
                    break;
            }
        } else {
            switch ($type) {
                case 1: //红包 展示需要多少人民币
                    $price_show = $price * $number;
                    break;
                case 2: //报单 展示需要多少CMC和人民币
                    //获取后台配置的CMC当前价格及报单属性
                    $cfg = new CmcpriceController();
                    $cmc_price = $cfg -> getPrice();
                    $attr = M("product") 
                        -> field("cmc, cny")
                        -> where("id = ". $id)
                        -> find();
                    // var_dump($attr);

                    $price_show = array("cmc" => $number * $attr['cmc'], "cny" => $number * $attr['cny'] * $cmc_price);
                    break;
                case 3: //重消 展示需要多少人民币
                    $price_show = $price * $number;
                    break;
                default:
                    # code...
                    break;
            }
        }

        return $price_show;
    }

    public function getIntegralPrice() {
        $total_price = I("total_price");
        $cmc = new CmcpriceController();
        $cmc_price = $cmc -> getPrice();
        $price = round($total_price / $cmc_price, 2);
        // var_dump($price);
        exit($price);
    }

    //发红包或积分
     function release($type, $id) {
        $price = M()
            -> table("currency_shop_order as o")
            -> field("o.total_money, o.number, o.order, o.cmc, p.integral, p.out, p.price, u.pid, u.id")
            -> join("left join currency_product as p on p.id = o.product_id")
            -> join("left join currency_users as u on o.user_id = u.id")
            -> where("o.id = ". $id)
            -> find();

        $return = false;

        switch ($type) {
            case '1': //红包
                $data['outs'] = $price['out'];
                $data['provide'] = 0;
                $data['time'] = time();
                $data['user_id'] = $price['id'];
                $data['number'] = $price['price'];

                $bonus_dis = new \Common\Controller\BonusController();

                $bonus_m = M("bonus");
                $bonus_m -> startTrans();

                if ($price['number'] > 1) {

                    for ($i=0; $i < $price['number']; $i++) {
                        $data1[] = $data;
                    }

                    $res = $bonus_m -> addAll($data1);

                    if ($res) {
                        $m = 0;
                        for ($i=0; $i < $price['number']; $i++) {
                            $dis = $this -> distribution($bonus_dis, $price['id'], $price['pid'], $price['price'], $price['order']);
                            if ($dis != true) {
                                break;
                            }
                            $m ++;
                        }
                        if ($m >= $price['number'] - 1) {
                            $bonus_m -> commit();
                            $return = true;
                        } else {
                            $bonus_m -> rollback();
                        }
                    } else {
                        $this -> error .= "批量发放红包失败";
                    }

                } else {
                    $res = $bonus_m -> add($data);
                    if ($res) {

                        $dis = $this -> distribution($bonus_dis, $price['id'], $price['pid'], $price['price'], $price['order']);
                        if ($dis != true) {
                            $bonus_m -> rollback();
                        } else {
                            $bonus_m -> commit();
                            $return = true;
                        }
                    } else {
                        $this -> error .= "发放红包失败";
                    }
                }

                break;
            case '2': //报单
                $data1['user_id'] = $price['id'];
                $data1['repeats'] = 0;
                $data1['water'] = 0;
                $data1['time'] = time();
                $data1['interest'] = 0;
                $data1['releases'] = 0;
                $data1['time_end'] = strtotime("-0 year -6 month -0 day");

                $integral_m = M("integral");

                if ($price['number'] > 1) {
                    for ($i=0; $i < $price['number']; $i++) {
                        $data[] = array_merge(['number' => $price['integral'], 'number_all' => $price['integral'], 'price' => $price['cmc']], $data1);
                    }

                    $res = $integral_m -> addAll($data);
                    if ($res) {
                        $return = true;
                    } else {
                        $this -> error = "批量发放积分失败";
                    }
                } else {
                    $data['price'] = $price['cmc'];
                    $data['number'] = $price['integral'];
                    $data['number_all'] = $price['integral'];
                    $data = array_merge($data1, $data);
                    $res = $integral_m -> add($data);
                    if ($res) {
                        $return = true;
                    } else {
                        $this -> error .= "发放积分失败";
                    }
                }
                // var_dump($data);
                break;
            case '3': //重消
                $return = true;
            // $this -> success("确认收货");
            default:

                break;
        }

        return $return;
    }

//红包分销
     function distribution($bonus_dis, $id, $pid, $price, $order) {
        $bonus_dis -> setUser(['id' => $id, 'pid' => $pid]);

        $bonus_dis -> setMoney($price);
        $bonus_dis -> setOrder($order);
        $res_dis = $bonus_dis -> getParent();
        if ($res_dis != true) {
            $this -> error .= $bonus_dis -> getError();
        }

        return $res_dis;
    }


}