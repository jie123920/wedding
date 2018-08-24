<?php
namespace app\modules\wedding\controllers;
use \app\helpers\myhelper;
use \app\modules\shop\models\GoodsSku;
use \app\modules\shop\models\GoodsCategory;
use \app\modules\shop\models\Goods;
use \app\modules\shop\models\ShopOrder;
use app\modules\shop\models\GoodsProperties;
use app\modules\shop\models\ShopOrderProduct;
use app\modules\shop\models\ShopCoupon;
use app\Library\ShopPay\ShopPay;
use app\modules\wedding\services\Cart;
use Yii;

class OrderController extends CommonController
{
    public $defaultAction = 'index';
    public $promotion = '';
    
    public function init()
    {
        parent::init();
        $this->layout = '@module/views/' . GULP . '/public/main-shop.html';
        $bread[] = [
            'url'=>'',
            'name'=>  \Yii::t('shop','Shipping&Payment')
        ];
        $this->view->params['bread'] = $bread;
    }

    /**
        用户中心直接购买未支付的订单
     */
    public function actionDirectBuy($order_id = ''){
        //$this->check_user('',true);
        $uid = $this->getUid();
        $back = \Yii::$app->params['MY_URL']['BS'].'/order/success';
        $project_back = 'bycouturier';
        
        if ($order_id){
            $shopPay = new ShopPay('3');
            $data = $shopPay->adap('shop', 'direct-buy', ['order_id'=>$order_id, 'uid'=>$uid, 'back'=>$back, 'project_back'=>$project_back]);
            echo json_encode($data);
        }
    }

    /**
     * 创建订单
     */
    public function actionCreateOrder() {
        $request = \Yii::$app->request;
        $shop_id = 3;

        $custom_size = "";
        if($request->get('from') == 'cart'){
            $result = $this->GetCart();
            $products = [];
            foreach ($result as $_result){
                $custom_size[$_result['item_id']] = json_encode($_result['custom_size']);
                $tmp['sku_id'] = $_result['item_id'];
                $tmp['amount'] = $_result['price'];
                $tmp['number'] = $_result['number'];
                $products[] = $tmp;
            }
            $products = json_encode($products);
            $custom_size = json_encode($custom_size);
        }else{
            $custom_size = $request->get('custom_size');
            if (is_array(json_decode($custom_size, true))){
                $custom_size = json_decode($custom_size, true);
                foreach ($custom_size as $key => &$child){
                    if ($child){
                        $child = myhelper::check_custom_size(json_encode($child));
                    }
                }
                $custom_size = json_encode($custom_size);
            }
            $products = $request->get('products');
        }

        $params = [
            'uid' => $this->getUid(),
            'lang' => $this->getLang(),
            'address_id' => $request->get('address_id'),
            'payment_country_id' => $request->get('payment_country_id'),
            'channel_id' => $request->get('channel_id'),
            'channel_name' => $request->get('channel_name'),
            'trans_type' => $request->get('trans_type'),
            'products' => $products,
            'remark' => $request->get('remark'),
            'coupon_code' => $request->get('coupon_code', ''),
            'custom_size' => $custom_size,
            'back' => \Yii::$app->params['MY_URL']['BS'].'/order/success',
            'project_back' => 'bycouturier',
            'event_date' => $request->get('event_date', ''),
            'refer' => $request->get('refer', ''),
            'ads'=> $this->cookies->get("ads","") ? $this->cookies->get("ads","")->value : ""
        ];
        
        $shopPay = new ShopPay($shop_id);
        $data = $shopPay->adap('pay', 'create-order', $params);

        echo json_encode($data);
    }



    /**
     * 创建订单
     */
    public function actionCreateOrderGuest() {
        $request = \Yii::$app->request;
        $shop_id = 3;

        $custom_size = "";
        if($request->get('from') == 'cart'){
            $result = $this->GetCart();
            $products = [];
            foreach ($result as $_result){
                if($_result){
                    if($_result['number'] > 0){//wdx 0615
                        $custom_size[$_result['item_id']] = json_encode($_result['custom_size']);
                        $tmp['sku_id'] = $_result['item_id'];
                        $tmp['amount'] = $_result['price'];
                        $tmp['number'] = $_result['number'];
                        $products[] = $tmp;
                    }

                }

            }
            $products = json_encode($products);
            $custom_size = json_encode($custom_size);
        }else{
            $custom_size = $request->get('custom_size');
            if (is_array(json_decode($custom_size, true))){
                $custom_size = json_decode($custom_size, true);
                foreach ($custom_size as $key => &$child){
                    if ($child){
                        $child = myhelper::check_custom_size(json_encode($child));
                    }
                }
                $custom_size = json_encode($custom_size);
            }
            $products = $request->get('products');
        }

        $params = [
            'uid' => $this->getUid(),
            'lang' => $this->getLang(),

            'full_name' => $request->get('fullname'),
            'country' => $request->get('country'),
            'country_id' => $request->get('country_id'),
            'state' => $request->get('state'),
            'country_code' => $request->get('country_id'),
            'city' => $request->get('city'),
            'email' => $request->get('email'),
            'postal_code' => $request->get('postal_code'),
            'phone' => $request->get('phone'),
            'shipping_address_1' => $request->get('address'),
            'shipping_address_2' => $request->get('address2'),

            'payment_country_id' => $request->get('payment_country_id'),
            'channel_id' => $request->get('channel_id'),
            'channel_name' => $request->get('channel_name'),
            'trans_type' => $request->get('trans_type'),
            'products' => $products,
            'remark' => $request->get('remark'),
            'coupon_code' => $request->get('coupon_code', ''),
            'custom_size' => $custom_size,
            'back' => \Yii::$app->params['MY_URL']['BS'].'/order/success',
            'project_back' => 'bycouturier',

            'event_date' => $request->get('event_date', ''),
            'refer' => $request->get('refer', ''),
            'ads'=> $this->cookies->get("ads","") ? $this->cookies->get("ads","")->value : "",
            'temp_uid'=> $this->cookies->get("temp_uid","") ? $this->cookies->get("temp_uid","")->value : "",


        ];

        $shopPay = new ShopPay($shop_id);
        $data = $shopPay->adap('pay', 'create-order-guest', $params);
        if(isset($data['data'])){
            if(isset($data['data']['orderid'])){
                $sign = md5("3".$data['data']['orderid'].'order-guest');
                $data['data']['order_info_url'] = Yii::$app->params['MY_URL']['BS']."/order/order-info?order_id=".$data['data']['orderid']."&sign=".$sign;
            }
        }
        echo json_encode($data);
    }


    /**
     * 针对活动，如果最终的支付价格为0.00，则终止跳往支付系统
     * 2017年5月27日 上午10:51:08
     * @author liyee
     * @param unknown $orderid
     */
    private function orderEnd($orderid){
        $result['code'] = 30001;
        $result['message'] = 'success';
        
        $shoporder = ShopOrder::find()->where(['id'=>$orderid])->andWhere(['<>', 'status', 1])->one();
        if ($shoporder){
            $shoporder->status = 1;
            $coupon_code = $shoporder->coupon_code;
            if ($shoporder->save()){
                if ($coupon_code){
                    $this->removeCoupon($coupon_code);
                }else {
                    $result['code'] = 30002;
                    $result['message'] = 'fail';
                }
            }
        }else {
            $result['code'] = 30002;
            $result['message'] = 'fail';
        }

        return $result;
    }
    
    /**
     * 订单支付成功，消除次数
     * 2017年5月26日 下午8:55:58
     * @author liyee
     */
    private function removeCoupon($coupon_code){
        $shopcoupon = ShopCoupon::find()->where(['code'=>$coupon_code])->one();
        $use_times = $shopcoupon->use_times;
        if ($use_times>0){
            $shopcoupon->use_times = $use_times-1;
            $shopcoupon->save();
        }
    }
    
    /**
     * 根据订单购买的物品给商品添加相应的属性
     * 2017年3月24日 下午3:46:08
     * @author liyee
     * @param unknown $products
     */
    private function orderProperties($products, $order_id){
        $products = json_decode($products, true);
        foreach ($products as $items){
            $goodid = $items['goods_id'];
            $name = '1';
            $value = $items['number'];
            $sku_id = $items['goods_sku_id'];
            $price = $items['price'];
            $color = isset($items['color'])?$items['color']:'';
            $size = isset($items['size'])?$items['size']:'';
            
            $this->addProperties($goodid, $name, $value);
            $this->orderProduct($order_id, $goodid, $sku_id, $name, $price, $value, $color, $size);
        }
    }
    
    /**
     * 订单对应商品详情
     * 2017年4月19日 上午10:39:32
     * @author liyee
     */
    private function orderProduct($order_id, $good_id, $sku_id, $name, $price, $num, $color, $size, $currency='USD'){ 
        $time = time();
        $shoporderproduct = ShopOrderProduct::find()->where(['order_id' => $order_id, 'good_id' => $good_id, 'sku_id' => $sku_id])->one();
        if (!$shoporderproduct){
            $shoporderproduct = new ShopOrderProduct();
            $shoporderproduct->order_id = (int)$order_id;
            $shoporderproduct->good_id = (string)$good_id;
            $shoporderproduct->sku_id = (string)$sku_id;
            $shoporderproduct->name = $name;
            $shoporderproduct->price = $price;
            $shoporderproduct->num = (int)$num;
            $shoporderproduct->color = (string)$color;
            $shoporderproduct->size = (string)$size;
            $shoporderproduct->currency = $currency;
            $shoporderproduct->updatetime = $time;
            $shoporderproduct->createtime = $time;
            
            $shoporderproduct->save();
        }
        
    }
    
    /**
     * 给商品添加属性（默认销量）
     * 2017年3月24日 下午3:32:36
     * @author liyee
     * @param unknown $goodid
     * @param unknown $name
     * @param unknown $value
     */
    private function addProperties($goodid, $name, $value){
        $time = time();
        $goodsproperties = GoodsProperties::find()->where(['goods_id'=>$goodid, 'name'=>$name])->one();
        if (!$goodsproperties){
            $goodsproperties = new GoodsProperties();
            $goodsproperties->goods_id = $goodid;
            $goodsproperties->name = $name;
            $goodsproperties->createdtime = $time;
        }else {
            $old_value = $goodsproperties->value;
            $value = $old_value+$value;
        }
        switch ($name){
            case 1:
                $goodsproperties->value = (string)$value;
                $goodsproperties->updatedtime = $time;
                break;
            default:
                ;
        }
        $goodsproperties->save();       
    }
    
    /**
     * 验证订单是否在有效期内6h过期
     * 2017年6月1日 下午6:45:12
     * @author liyee
     */
    private function orderOvertime($createtime = ''){
        $result = false;
        $time = time();
        if ($createtime){
            if ($createtime+21600 >= $time){
                $result = true;
            }
        }
    
        return $result;
    }


    private  function GetCart(){
        $shop_id = $this->getShopId();
        $uid     = $this->getUid();
        $lang    = $this->getLang();

        $cartService = new Cart();
        if(!$uid){
            $items = [];
            $cookie_read = \YII::$app->request->cookies;
            if($cookie_read->has('cart')){
                $cookie_cart = $cookie_read->get('cart');
                $cookie_cart  = unserialize(gzdecode($cookie_cart->value));
                foreach ($cookie_cart as $id => $c_cart){
                    $items[$id] = $c_cart->toArray();
                }
            }

            if (empty($items)) {
                $items = '';
            } else {
                $items = serialize($items);
            }
            $result = $cartService->cartCookieList($shop_id, $lang, $items);
        } else {
            $result = $cartService->cartDbList($shop_id, $lang, $uid);
        }

        return $result;
    }


    /**
     * 补充地址信息
     *
     * @return void
     */
    public function actionShipping()
    {
        $request = \Yii::$app->request;

        $custom_size_str = [];
        if($request->get('from') == 'cart'){
            $result = $this->GetCart();
            //var_dump($result);exit;
            $tempresult = [];
            foreach ($result as $_result){
                if($_result){
                    if($_result['number'] > 0){
                        $custom_size[$_result['item_id']] = json_encode($_result['custom_size']);
                        $tempresult[] = $_result;
                    }
                  
                }
                
            }
            //var_dump($tempresult);
            $items = json_encode($tempresult);
        }elseif($request->get('from') == 'detail'){
            $items = $request->get('items');
            if (is_array(json_decode($items, true))){
                $items = json_decode($items, true);
                foreach ($items as $key => &$child){
                    if ($child['custom_size']){
                        $custom_size_str[$key] = $child['custom_size'];
                        $c_tmp = json_decode(myhelper::check_custom_size(json_encode($child['custom_size'])),true);
                        $child['custom_size'] = $c_tmp;
                    }else{
                        unset($child['custom_size']);
                    }
                }
                $items = json_encode($items);
            }
        }else{
            exit();
        }

        $custom_size_str = json_encode($custom_size_str);

        $shopPay = new ShopPay('bycouturier');
        $pay_channel = $shopPay->adap('shop', 'pay-channel', ['shop_id'=>2]);
        $clientip = myhelper::get_client_ip();
        $countries = $shopPay->adap('shop', 'countries', ['clientip' => $clientip]);
        $country_to_channel = $shopPay->adap('shop', 'country-to-channel', ['shop_id'=>2]);
        $promotion = $shopPay->promotion();
        $product_info = $shopPay->adap('shop', 'product-info', ['items' => $items, 'custom_id' => CUSTOM_ID,'lang'=>LANG_SET]);
        $product = $product_info['data']['product'];
        $iscustom = $product_info['data']['iscustom'];
        $clientip = myhelper::get_client_ip();
       

        /*20180608*/
         $jsonlist = json_encode($product);//wdx 0606
         $shopPay = new ShopPay(3);
         $activity_list = $shopPay->ActivityInfo($jsonlist);//wdx 0606
         //var_dump($activity_list);exit;
         $actlist = [];
         if($activity_list['code'] == 0 && isset($activity_list['data']))
            $actlist = $activity_list['data'];
        
        $act_products = [];
        $aff_products = [];
        $act_info = [];
        $re_price = 0;
        if($actlist){
            
            foreach ($actlist as $key => $value) {
                if(isset($value[2])){
                    $act_info = $value[2];
                    if(isset($value[2]['re_price']))
                    $re_price += $value[2]['re_price'];
                }
                if(isset($value[0])){
                    $act_products = $value[0];//array_merge($act_products,$value[0]);
                }
                if(isset($value[1])){
                    $aff_products = $value[1];//array_merge($aff_products,$value[1]);
                }
            }
           
        }
        $total_ori = 0;
        $total = 0;
        $amount = 0;
        if($product){
            foreach ($product as $key => $item) {
                if($item){
                     $price = $item['price'];
                     $total += $price;
                     if($act_products){
                        foreach ($act_products as $k => $val) {
                            //var_dump($k);var_dump($item['id']);
                            if($k == $item['id']){
                                if($act_info){
                                    $product[$key]['activity_info'] = $act_info['name'];

                                 }
                                
                            }
                        }
                     }
                     if($aff_products){
                        foreach ($aff_products as $k => $val) {
                            //var_dump($k);var_dump($item['id']);
                            if($k == $item['id']){
                                if($act_info){
                                    $product[$key]['activity_info'] = $act_info['name'];
                                    if($act_info['mode'] == 11){
                                        $product[$key]['activity_discounts'] =  $price*(1-$act_info['affected_discount']);
                                    }else if($act_info['mode'] == 12){
                                        $product[$key]['activity_discounts'] =  $price*(1-$act_info['affected_discount']);
                                    }
                                }
                               
                            }
                        }
                     }
                }
            }
            $amount  = $total;
            $total = $total_ori =  round($total*THINK_RATE_M, 2);

            $re_price = round($re_price*THINK_RATE_M, 2);
            $total -=$re_price;
            
        }
        $freight_countries = $shopPay->adap('shop', 'freight-countries', ['clientip' => $clientip,'amount'=>$amount]);

        //var_dump($actlist); 
        //婚纱网址独有的线下支付方式
        $pay_channel = $pay_channel['data'];
        $pay_channel[1001] =
        [
            'id' => '1001',
            'pay_way' => 'Wire Transfer',
            'img' => 'https://cdn-image.mutantbox.com/201801/2969f4b6c2919345f23570381d8845bd.png',
            'img_url' => 'https://cdn-image.mutantbox.com/201801/2969f4b6c2919345f23570381d8845bd.png',
        ];
        $pay_channel[1002] =
        [
            'id' => '1002',
            'pay_way' => 'Western Union',
            'img' => 'https://cdn-image.mutantbox.com/201801/b2b0bb322dfa4c7d3b5eee13be151d77.jpg',
            'img_url' => 'https://cdn-image.mutantbox.com/201801/b2b0bb322dfa4c7d3b5eee13be151d77.jpg',
        ];

        $country_to_channel = $country_to_channel['data'];

        foreach ($country_to_channel as &$d){
            if(isset($d['channel_way_id']) && !in_array('1001',$d['channel_way_id']))
                array_push($d['channel_way_id'],'1001');
            if(isset($d['channel_way_id']) && !in_array('1002',$d['channel_way_id']))
                array_push($d['channel_way_id'],'1002');
        }

        if($this->is_login == 0){
            $tpl = 'shipping_guest';
        }else{
            $tpl = 'shipping';
        }
        //$list = myhelper::productSort($product);
        //echo "<pre>";print_r($list);exit;
        return $this->render('/shop/'.$tpl.'.html', [
            'pay_channel' => json_encode($pay_channel),
            'country_to_channel' => json_encode($country_to_channel),
            'promotion' => json_encode($promotion['data']),
            'product_info' => $product,
            //'custom_size' => $custom_size,
            'iscustom' => $iscustom,
            'list' => $countries['data']['list'],
            'default_id' => $countries['data']['default']['id'],
            'freight_countries' => json_encode($freight_countries['data']),
            'from'=>$request->get('from'),
            'custom_size_str'=>$custom_size_str,
            're_price' =>$re_price?$re_price:0.00,
            'activity' =>$act_info,
        ]);
    }
    
    /**
     * 获取运费金额
     * dell
     */
    public function actionFreightNew(){
        $country = \Yii::$app->request->get('country_id');
        $type = \Yii::$app->request->get('type');
        $amount = floatval(\Yii::$app->request->get('amount'));
        $weight = \Yii::$app->request->get('weight');
        $shopPay = new ShopPay('bycouturier');
        $data = $shopPay->adap('shop', 'freight-new', ['country_id' => $country, 'type' => $type, 'amount' => $amount, 'weight' => $weight,'shop_id'=>3]);
        
        echo json_encode($data);
    }
    /**
     * 获取运费金额
     * dell
     */
    public function actionFreightNewNoPromo(){
        $country = \Yii::$app->request->get('country_id');
        $type = \Yii::$app->request->get('type');
        $amount = floatval(\Yii::$app->request->get('amount'));
        $weight = \Yii::$app->request->get('weight');
        $shopPay = new ShopPay('bycouturier');
        $data = $shopPay->adap('shop', 'freight-new-no-promo', ['country_id' => $country, 'type' => $type, 'amount' => $amount, 'weight' => $weight,'shop_id'=>3]);
        
        echo json_encode($data);
    }
    
    /**
     * 支付成功返回页面
     * 2017年8月19日 上午11:33:26
     * @author liyee
     * @return string
     */
    public function actionSuccess(){
        $key = \Yii::$app->params['TOKEN']['projectKey'];
        $request = \Yii::$app->request;
        $gameid = $request->get('gameid');
        $orderid = $request->get('orderid');
        $from = $request->get('from','adyen');
        $success = $request->get('success', 'true');
        $base_url = \Yii::$app->params['MY_URL']['BS'];
        $result = $this->GoogleTag($orderid);//var_dump($result);die;
        $orderinfo = [];
        if ($result){
            $orderinfo = $result;
        }
        $orderinfo = json_encode($orderinfo);
        $orderid = myhelper::DesDecryptNew($orderid, $key);
        $this->orderToStatus($orderid);
        if ($from == 'paymentwall'){
            return $this->render('pay.html', [
                'orderInfo' => $result,
                'success' => $success,
                'base_url' => $base_url,
            ]);
        }else {
            return $this->renderPartial('success.html',[
                'gameid' => $gameid,
                'orderid' => $orderid,
                'orderinfo' => $orderinfo,
                'from' => $from,
            ]);
        }
    }
    
    /**
     * 支付成功返回页面
     * 2017年8月19日 上午11:33:26
     * @author liyee
     * @return string
     */
    public function actionPay(){
        $request = \Yii::$app->request;
        $orderid = $request->get('orderid', '');
        $success = $request->get('success', 'false');
        $orderInfo = [];
        $base_url = \Yii::$app->params['MY_URL']['BS'];
        if ($orderid && $success=='true'){
            $uid      = $this->user_info['id'];
            $shopPay = new ShopPay('3');
            $response = $shopPay->adap('shop', 'order-info', ['orderid'=>$orderid, 'uid'=>$uid]);
            if (is_array($response)){
                if (isset($response['data'])){
                    $orderInfo = $response['data'];
                    $status = $shopPay->adap('shop', 'order-to-status', ['orderid'=>$orderInfo['orderid'], 'uid'=>$uid]);
                }
            }
        }
        
        return $this->render('pay.html', [
            'orderInfo' => $orderInfo,
            'success' => $success,
            'base_url' => $base_url,
        ]);
    }
    
    /**
     * 获取订单详情
     * 2017年8月14日 下午12:00:27
     * @author liyee
     */
    public function GoogleTag($orderid){
        $key = \Yii::$app->params['TOKEN']['projectKey'];
        if ($orderid = (myhelper::DesDecryptNew($orderid, $key))){
            $data = [];
            $order = ShopOrder::find()->select(['id','products','coupon_code','amount','freight','total_amount','payment_country_code'])->where(['id'=>$orderid])->one();
            $products = json_decode($order->products, true);
            
            $gtm = [];
            foreach ($products as $productValue){
                $good_info = Goods::findOne($productValue['goods_id']);
                $sku_info = GoodsSku::findOne($productValue['goods_sku_id']);
                
                $varient = '';
                foreach($sku_info->specValue as $v){
                    $varient .= $v->spec_value.",";
                }
                $varient = rtrim($varient,",");
                $brand = $good_info->brand;
                $category_info = '';
                if($good_info->categories){
                    $category = $good_info->categories[0];
                    $category_info = GoodsCategory::findOne($category->category_id);
                }
                
                $GTMINFO['name'] = $productValue['name'];
                $GTMINFO['id'] = $productValue['goods_sku_id'];
                $GTMINFO['price'] = $productValue['price'];
                $GTMINFO['brand'] = $brand?$brand->brand_name:'';
                $GTMINFO['category'] = $category_info?$category_info->name:'';
                $GTMINFO['variant'] = $varient;
                $GTMINFO['quantity'] = $productValue['number'];
                
                $gtm[] = (object)$GTMINFO;
            }
            
            $data['quantity'] = array_sum(array_column($products, 'number'));
            $data['ids'] = implode(',', array_column($products, 'goods_sku_id'));
            $data['total_amount'] = $order->total_amount;
            $data['orderid'] = $orderid;
            $data['amount'] = $order->amount;
            $data['coupon_code'] = $order->coupon_code;
            $data['freight'] = $order->freight;
            $data['payment_country_code'] = $order->payment_country_code;
            $data['gtm'] = $gtm;
            
            return $data;
        }
    }
    
    /**
     * 更新订单状态
     * 2017年8月14日 上午11:53:52
     * @author liyee
     */
    public function orderToStatus($id){
        $shopOrder = ShopOrder::findOne($id);
        if ($shopOrder){
            $status = $shopOrder->status;
            if ($status != 1){
                $shopOrder->status = 3;
                $shopOrder->save();
            }
        }
    }
    
    /**
     * 优惠码功能
     * 2017年9月4日 下午12:01:04
     * @author liyee
     * @return number[]|string[]|unknown[]|number[][]|string[][]|unknown[][]|array[]|mixed[]
     */
    public function actionVerifyByCode(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $request = \Yii::$app->request;
        $code = $request->get('code');
        $categories = $request->get('categories');
        $type = $request->get('type', 1);
        $activity_amount = $request->get('activity_amount', 0);
        $uid = $this->getUid();
        
        $shopPay = new ShopPay('bycouturier');
        $data = $shopPay->adap('shop', 'verify-by-code', ['uid' => $uid, 'code' => $code, 'categories' => $categories, 'type' => $type,'activity_amount'=>$activity_amount]);

        return $data;
    }



    public function actionOfflinePay(){

        $bread[] = [
            'url'=>'',
            'name'=>  \Yii::t('shop','Payment Information')
        ];
        $this->view->params['bread'] = $bread;

        $order_id = \Yii::$app->request->get("id");
        $shopPay = new ShopPay('3');
        $data = $shopPay->adap('shop', 'order-offline', ['orderid' => $order_id, 'uid' => $this->getUid()]);
        $order = [];
        if($data){
            if($data['code'] == 0){
                $order =  $data['data'];
            }
        }
        if(!$order){
            return $this->redirect(['/']);exit;
        }

        $pro = json_decode($order['products'],true);
        $number = 0;
        foreach ($pro as $_pro){
            $number +=$_pro['number'];
        }


        return $this->render('offline-pay.html',[
            'order_id'=>$order_id,
            'data'=>$order,
            'products'=>json_decode($order['products'],true),
            'number'=>$number
        ]);
    }

    public function actionOrderInfo()
    {
        $this->layout = '@module/views/'.GULP.'/public/user.html';
        $md5     = Yii::$app->request->get('sign', '');
        $order_id = Yii::$app->request->get('order_id', 0);

        $shopPay = new ShopPay('bycouturier');
        $returnData = $shopPay->OrderGuest($md5, $order_id);
        $orderList = [];
        if (is_array($returnData) && $returnData['code'] == 0) {
            $orderList[] = $returnData['data'];
        }else{
            return $this->redirect(['/']);
        }
        return $this->render('order-info.html', [
            'order_list' => $orderList,
        ]);
    }


    public function actionGrep($value='', $type) {
        return myhelper::grepcheck($value, $type);
    }
    
    /**
     * 获取用户id
     *
     * @return int
     */
    private function getUid()
    {
        return isset($this->user_info['id']) ? $this->user_info['id'] : 0;
    }
    
    /**
     * 获取商店id
     *
     * @return int
     */
    private function getShopId()
    {
        // todo:
        return SHOP_ID;
    }
    
    /**
     * 获取语言
     *
     * @return string
     */
    private function getLang()
    {
        return LANG_SET;
    }
    
    private function err($code, $message) {
        return ['code' => $code, 'message' => $message, 'data' => []];
    }    
}