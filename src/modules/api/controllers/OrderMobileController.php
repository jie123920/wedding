<?php
namespace app\modules\api\controllers;

use app\helpers\myhelper;
use app\Library\ShopPay\ShopPay;

class OrderMobileController extends CommonController
{
    public $defaultAction = 'index';
    public $payment_country_id;
    
    public function init()
    {
        parent::init();
        parent::behaviors();
        $this->enableCsrfValidation = false;
        if(!$this->is_login){
            return $this->result(10001);
        }
    }
    
    /**
     * 移动端方法适配
     * 2017年9月7日 下午7:09:30
     * @author liyee
     * @param unknown $subject
     * @param unknown $event
     * @param unknown $get
     * @return number[]|string[]|unknown[]|number[][]|string[][]|unknown[][]|array[]|mixed[]
     */
    public function actionRun(){
        $request = \Yii::$app->request;
        $get = $request->get();
        $post = $request->post();
        $params = array_merge($get, $post);

        $params['back'] = \Yii::$app->params['MY_URL']['M'].'/Success';
        $params['eback'] = \Yii::$app->params['MY_URL']['M'].'/Fail';
        if (isset($params['subject']) && isset($params['event'])){
            $subject = $params['subject'];
            $event = $params['event'];
            unset($params['subject']);
            unset($params['event']);
        }
        $shopPay = new ShopPay();
        $params = $this->addUid($event, $params);
        $params['lang'] = $this->getLang();
        $data = $shopPay->adap($subject, $event, $params);
        
        return $this->resultNew($data);
    }
    
    /**
     * 添加用户id
     * 2017年9月7日 下午7:20:01
     * @author liyee
     * @param unknown $event
     * @param unknown $get
     * @return number
     */
    private function addUid($event, $params){
        $events = [
            'create-order',
            'direct-buy',
            'list'
        ];
        
        if (in_array($event, $events)){
            if (isset($this->user_info['id'])){
                $params['uid'] = $this->user_info['id'];
            }else {
                $params['uid'] = 0;
            }
        }
        return $params;
    }
    
    /**
     * 获取国家列表及默认国际
     * 2017年8月16日 上午10:05:38
     * @author liyee
     */
    public function actionCountries(){
        $shopPay = new ShopPay();
        $data = $shopPay->adap('shop', 'countries');

        return $this->resultNew($data);
    }
    
    /**
     * 获取支付方式
     * 2017年8月16日 上午11:13:03
     * @author liyee
     */
    public function actionPayChannel(){
        $shopPay = new ShopPay();
        $data = $shopPay->payChannel();

        if($data['data']){
            $data['data'][1001] =
                [
                    'id' => '1001',
                    'pay_way' => 'Wire Transfer',
                    'img' => 'https://cdn-image.mutantbox.com/201801/2969f4b6c2919345f23570381d8845bd.png',
                    'img_url' => 'https://cdn-image.mutantbox.com/201801/2969f4b6c2919345f23570381d8845bd.png',
                ];
            $data['data'][1002] =
                [
                    'id' => '1002',
                    'pay_way' => 'Western Union',
                    'img' => 'https://cdn-image.mutantbox.com/201801/b2b0bb322dfa4c7d3b5eee13be151d77.jpg',
                    'img_url' => 'https://cdn-image.mutantbox.com/201801/b2b0bb322dfa4c7d3b5eee13be151d77.jpg',
                ];
        }


        return $this->resultNew($data);
    }
    
    /**
     * 获取国家和支付方式对应关系
     * 2017年8月16日 下午12:00:44
     * @author liyee
     */
    public function actionCountryToChannel(){
        $shopPay = new ShopPay();
        $pay_channel = $shopPay->countryToChannel();

        //婚纱网址独有的线下支付方式
        $data = $pay_channel['data'];


        foreach ($data as &$d){
            if(!in_array('1001',$d['channel_way_id']))
                array_push($d['channel_way_id'],'1001');
            if(!in_array('1002',$d['channel_way_id']))
                array_push($d['channel_way_id'],'1002');
        }

        $pay_channel['data'] = $data;
        return $this->resultNew($pay_channel);
    }



    /**
     * 获取支付方式
     * 2017年8月16日 上午11:13:03
     * @author liyee
     */
    public function actionPayList(){
        $shopPay = new ShopPay(2);
        $country = $shopPay->adap('shop', 'countries');
        $payway = $shopPay->payChannel();

        if($payway['data']){
            $payway['data'][1001] =
                [
                    'id' => '1001',
                    'pay_way' => 'Wire Transfer',
                    'img' => 'https://cdn-image.mutantbox.com/201801/2969f4b6c2919345f23570381d8845bd.png',
                    'img_url' => 'https://cdn-image.mutantbox.com/201801/2969f4b6c2919345f23570381d8845bd.png',
                ];
            $payway['data'][1002] =
                [
                    'id' => '1002',
                    'pay_way' => 'Western Union',
                    'img' => 'https://cdn-image.mutantbox.com/201801/b2b0bb322dfa4c7d3b5eee13be151d77.jpg',
                    'img_url' => 'https://cdn-image.mutantbox.com/201801/b2b0bb322dfa4c7d3b5eee13be151d77.jpg',
                ];
        }


        $country_payway = $shopPay->countryToChannel();

        if($country_payway['data']){
            foreach ($country_payway['data'] as &$d){
                if(!in_array('1001',$d['channel_way_id']))
                    array_push($d['channel_way_id'],'1001');
                if(!in_array('1002',$d['channel_way_id']))
                    array_push($d['channel_way_id'],'1002');
            }
        }

        $data['country'] = $country['data']['list'];
        $data['country_payway'] = $country_payway['data'];
        $data['payway'] = $payway['data'];
        foreach ($data['country'] as &$_country){
            if($_country['id'] == $country['data']['default']['id']){
                $_country['selected'] = "selected";break;
            }
        }

        return $this->result(0, $data, '');
    }

    /**
     * 根据sku_id获取商品详情
     * 2017年8月17日 下午4:52:44
     * @author liyee
     */
    public function actionProductInfo(){
        $request = \Yii::$app->request;
        $items = $request->get('items');
        
        $shopPay = new ShopPay();
        $data = $shopPay->productInfo($items);
        
        return $this->resultNew($data);
    }

    /**
     * 创建订单
     * 2017年8月18日 上午11:26:41
     * @author liyee
     * @return number[]|string[]|unknown[]|number[][]|string[][]|unknown[][]|array[]|mixed[]
     */
    public function actionCreateOrder() {
        $request = \Yii::$app->request;
        $uid = $this->getUid();
        $lang = $this->getLang();
        $address_id = $request->get('address_id');
        $payment_country_id = $request->get('payment_country_id');
        $channel_id = $request->get('channel_id');
        $trans_type = $request->get('trans_type');
        $products = json_decode($request->get('products'), true);
        $remark = $request->get('remark');
        $coupon_code = $request->get('coupon_code', '');
        $device = $request->get('device', 'mobile');
        $custom_size = $request->get('custom_size');
        $ads = $this->cookies->get("ads","") ? $this->cookies->get("ads","")->value : "";
        $shopPay = new ShopPay();
        $data = $shopPay->createOrder($uid, $lang, $address_id, $payment_country_id, $channel_id, $trans_type, $products, $remark, $coupon_code, $device,$custom_size,$ads);
        
        return $this->resultNew($data);
    }


    public function actionCreateOrderGuest() {
        $request = \Yii::$app->request;

        $params = [
            'shop_id'=>3,
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
            'trans_type' => $request->get('trans_type'),
            'products' => $request->get('products'),
            'remark' => $request->get('remark'),
            'coupon_code' => $request->get('coupon_code', ''),
            'device' => $request->get('device', 'mobile'),
            'custom_size' => $request->get('custom_size',''),
            'back' => \Yii::$app->params['MY_URL']['M'].'/Success',
            'eback' => \Yii::$app->params['MY_URL']['M'].'/Fail',

            //'event_date' => $request->get('event_date', ''),
            //'refer' => $request->get('refer', ''),
            'ads'=> $this->cookies->get("ads","") ? $this->cookies->get("ads","")->value : ""
        ];

        $shopPay = new ShopPay();
        $data = $shopPay->createOrderGuest($params);

        if(isset($data['data'])){
            if(isset($data['data']['orderid'])){
                $data['data']['order_sign'] = md5("3".$data['data']['orderid'].'order-guest');
            }
        }

        return $this->resultNew($data);
    }


    /**
     * 用户中心直接购买
     * 2017年8月21日 下午5:24:08
     * @author liyee
     * @param string $order_id
     */
    public function actionDirectBuy(){
        $request = \Yii::$app->request;
        $uid = $this->getUid();
        $order_id = $request->get('order_id');        
        $device = $request->get('device', 'pc');
        
        $shopPay = new ShopPay();
        $data = $shopPay->directBuy($uid, $order_id, $device);
        
        return $this->resultNew($data);
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
        $shopPay = new ShopPay();
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
     * 获取运费价格
     */
    public function actionFreight()
    {
        $country = \Yii::$app->request->get('country_id');
        $type = \Yii::$app->request->get('type');
        $amount = \Yii::$app->request->get('amount');
        
        $shopPay = new ShopPay();
        $data = $shopPay->freight($country, $type, $amount);
        
        return $this->resultNew($data);
    }


    /**
     * 国家运费信息
     * @return array
     */
    public function actionFreightCountries()
    {
        $clientip = myhelper::get_client_ip();
        $country_id = \Yii::$app->request->get('country_id');
        $amount = \Yii::$app->request->get('amount');
        $shopPay = new ShopPay();
        $freight_countries = $shopPay->adap('shop', 'freight-countries', ['clientip' => $clientip,'country_id'=>$country_id,'amount'=>$amount]);
        return $this->resultNew($freight_countries);
    }


    /**
     * Lists all GoodsPromotion models.
     * @return mixed
     */
    public function actionPromotion(){
        $shopPay = new ShopPay();
        $data = $shopPay->promotion();
        
        return $this->resultNew($data);
    }
    
    /**
     * 汇率货币列表
     * 2017年4月25日 下午4:28:09
     * @author liyee
     */
    public function actionCurrencyList() {
        $shopPay = new ShopPay();
        $data = $shopPay->currencyList();
        
        return $this->resultNew($data);
    }
    
    /**
     * 订单列表接口
     * 2017年8月28日 下午4:03:44
     * @author liyee
     */
    public function actionList(){
        $uid = $this->getUid();
        
        $shopPay = new ShopPay();
        $data = $shopPay->listInfo($uid);
        
        return $this->resultNew($data);
    }
    
    /**
     * 优惠码功能
     * 2017年9月4日 下午12:01:04
     * @author liyee
     * @return number[]|string[]|unknown[]|number[][]|string[][]|unknown[][]|array[]|mixed[]
     */
    public function actionVerifyByCode(){
        $request = \Yii::$app->request;
        $code = $request->get('code');
        $categories = $request->get('categories');
        $type = $request->get('type', 1);
        $activity_amount = $request->get('activity_amount', 0);
        $uid = $this->getUid();
        
        $shopPay = new ShopPay();
        $data = $shopPay->verifyByCode($uid, $code, $categories, $type,$activity_amount);
        
        return $this->resultNew($data);        
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
        // todo:
        return LANG_SET;
    }



    public function actionOrderInfo()
    {
        $md5     = \Yii::$app->request->get('order_sign', '');
        $order_id = \Yii::$app->request->get('order_id', 0);

        $shopPay = new ShopPay('bycouturier');
        $returnData = $shopPay->OrderGuest($md5, $order_id);
        return $this->resultNew($returnData);
    }

    //wdx
    public function actionUserOrderInfo()
    {
        $shop_id=3;
        $uid = $this->getUid();
        $order_id = \Yii::$app->request->get('order_id', 0);
        $shopPay = new ShopPay('bycouturier');
        $returnData = $shopPay->OrderInfo($uid, $order_id,$shop_id);
        return $this->resultNew($returnData);
    }
}
