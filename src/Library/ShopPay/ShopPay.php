<?php 
namespace app\Library\ShopPay;

class  ShopPay extends Common{    
    private $shop;
    private $pay;
    
    public function __construct($shop_id = '3'){
        parent::__construct($shop_id);
        $this->shop = new Shop($shop_id);
        $this->pay = new Pay($shop_id);
    }
    
    /**
     * 方法适配
     * 2017年9月7日 下午3:57:48
     * @author liyee
     * @param unknown $bottom(shop,pay)
     * @param unknown $mode
     * @param array $params
     * @return unknown
     */
    public function adap($bottom, $mode, $params = []){
        $to = 'order-new/'.$mode;
        $params['shop_id'] = isset($params['shop_id'])?$params['shop_id']:$this->shop_id;
        $method = $bottom.'Info';
        return $this->$bottom->$method($to, $params, 'POST');
    }
    
    /**
     * 根据sku_id获取商品详情
     * 2017年8月17日 下午4:52:44
     * @author liyee
     */
    public function productInfo($items){
        return $this->shop->shopInfo('order-new/product-info', [
            'items' => $items,
            'shop_id' => $this->shop_id
        ]);
    }
    
    /**
     * 获取运费价格
     */
    public function freight($country, $type, $amount)
    {
        return $this->shop->shopInfo('order-new/freight', [
            'country_id' => $country,
            'type' => $type,
            'amount' => $amount,
        ]);
    }
    
    /**
     * 优惠信息
     * @return mixed
     */
    public function promotion(){
        return $this->shop->shopInfo('order-new/promotion',['shop_id'=>3]);
    }
    
    /**
     * 汇率货币列表
     * 2017年4月25日 下午4:28:09
     * @author liyee
     */
    public function currencyList() {
        return $this->shop->shopInfo('order-new/currency-list');
    }
    
    /**
     * 电商订单列表接口
     * 2017年8月28日 下午4:03:44
     * @author liyee
     */
    public function listInfo($uid, $page = 1, $pageSize = 10){
        return $this->shop->shopInfo('order-new/list', [
            'uid' => $uid,
            'shop_id' => $this->shop_id,
            //'shop_id' => 2,//TODO   test
            'page' => $page,
            'per-page' => $pageSize,
        ]);
    }


    public function OrderGuest($md5,$order_id){
        return $this->shop->shopInfo('order-new/order-guest', [
            'md5' => $md5,
            'shop_id' => 3,
            'order_id' => $order_id
        ]);
    }
    //wdx 0628
    public function OrderInfo($uid,$order_id,$shop_id=3){
        return $this->shop->shopInfo('order-new/order-user', [
            'uid' => $uid,
            'shop_id' => $shop_id,
            'order_id' => $order_id
        ]);
    }

    /**
     * 优惠码功能
     * 2017年9月7日 下午12:29:19
     * @author liyee
     * @param unknown $uid
     * @param unknown $code
     * @param unknown $categories
     * @param unknown $type
     * @return boolean|mixed
     */
    public function verifyByCode($uid, $code, $categories, $type,$activity_amount=0){
        return $this->shop->shopInfo('order-new/verify-by-code', [
            'uid' => $uid,
            'code' => $code,
            'categories' => $categories,
            'type' => $type,
            'shop_id' => $this->shop_id,
            'activity_amount' => $activity_amount,
        ]);
    }
    
    /**
     * 获取支付方式
     * 2017年8月16日 上午11:13:03
     * @author liyee
     */
    public function payChannel(){
        return $this->pay->payInfo('order-new/pay-channel', [
            'shop_id' => $this->shop_id,
        ]);
    }
    
    /**
     * 获取国家和支付方式对应关系
     * 2017年8月16日 下午12:00:44
     * @author liyee
     */
    public function countryToChannel(){
        return $this->pay->payInfo('order-new/country-to-channel', [
            'shop_id' => $this->shop_id,
        ]);
    }
    
    /**
     * 创建订单
     * 2017年8月18日 上午11:26:41
     * @author liyee
     * @return number[]|string[]|unknown[]|number[][]|string[][]|unknown[][]|array[]|mixed[]
     */
    public function createOrder($uid, $lang, $address_id, $payment_country_id, $channel_id, $trans_type, $products, $remark, $coupon_code, $device = 'pc',$custom_size="",$ads) {
        return $this->pay->payInfo('order-new/create-order', [
            'shop_id' => $this->shop_id,
            'uid' => $uid,
            'lang' => $lang,
            'address_id' => $address_id,
            'payment_country_id' => $payment_country_id,
            'channel_id' => $channel_id,
            'trans_type' => $trans_type,
            'products' => is_array($products)?json_encode($products):$products,
            'remark' => $remark,
            'coupon_code' => $coupon_code,
            'custom_size' => $custom_size,
            'device' => $device,
            'back' => \Yii::$app->params['MY_URL']['M'].'/Success',
            'eback' => \Yii::$app->params['MY_URL']['M'].'/Fail',
            'ads'=>$ads
        ]);
    }
    public function createOrderGuest($data) {
        return $this->pay->payInfo('order-new/create-order-guest', $data);
    }
    /**
     * 根据电商订单id支付
     * 2017年9月6日 下午5:23:18
     * @author liyee
     */
    public function directBuy($uid, $order_id, $device){
        return $this->pay->payInfo('order-new/create-order', [
            'uid' => $uid,
            'order_id' => $order_id,
            'shop_id' => $this->shop_id,
            'device' => $device,
        ]);
    }


    /**
     * 根据购物车产品信息获取活动详情
     * 2018年6月6日 下午4:52:44
     * @author  wdx
     */
    public function ActivityInfo($items){
        return $this->shop->shopInfo('order-new/activity-info', [
            'items' => $items,
            'shop_id' => $this->shop_id
        ],'POST');
    }

}