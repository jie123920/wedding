<?php
namespace app\modules\shop\controllers;
use Yii;
use yii\web\Controller;
use \app\helpers\myhelper;
use \app\modules\shop\models\Goods;
use \app\modules\shop\models\GoodsSku;
use \app\modules\shop\models\ShopOrder;
use \app\modules\shop\models\ShopOrderUtil;
use \app\modules\shop\models\GoodsTransPrice;
use \app\modules\shop\models\Region;
use \app\modules\shop\models\GoodsPromotion;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use app\modules\shop\models\UserAddress;
use phpseclib\Crypt\DES;
use app\modules\shop\models\GoodsPromotionSearch;
use app\Library\ShopPay\ShopPay;
use app\modules\shop\models\ShopCoupon;
use app\modules\shop\models\GoodsCategory;
use app\modules\shop\models\GoodsCart;

class OrderNewController extends Controller
{
    public $defaultAction = 'index';
    public $payment_country_id;
    public $callback = null;
    public $key = '1Fq9uZj9JeJPuje2';
    
    public function init()
    {
        $this->enableCsrfValidation = false;
        $this->callback = Yii::$app->request->get('callback',null);
        Yii::$app->response->format = $this->callback ? \yii\web\Response::FORMAT_JSONP : \yii\web\Response::FORMAT_JSON;
        $params = array_merge(Yii::$app->request->get(),Yii::$app->request->post());
        if(!$this->decode($params)){
            $result =  $this->result(1,[],'signature is error!');
            echo json_encode($result);
            exit;
        }
    }
    
    public function result($code=0,$data = [],$msg=''){
        if($this->callback){
            return array(
                    'callback' => $this->callback,
                    'data' => [
                            'code' => $code,
                            'message' => $msg,
                            'data'=>$data
                    ]
            );
        }else{
            return array(
                    'code' => $code,
                    'message' => $msg,
                    'data'=>$data
            );
        }
    }
    
    //验证接受参数
    public function decode($params){
        if(isset($params['signature'])){
            $signature = $params['signature'];
            unset($params['signature']);
            unset($params['api_access_key']);
            sort($params,SORT_STRING);
            $sign = md5($this->key.implode("", $params));
            if($signature == $sign){
                return true;
            }
        }
        return false;
    }
    
    /**
     * 根据用户id查询用户的优惠码是否使用
     * 2017年5月26日 下午8:42:43
     * @author liyee
     * @param unknown $from_id
     * @param unknown $code
     */
    protected function nunByuid($code, $uid){
        $status = [1,11,12,13];
        $shoporder = ShopOrder::find()->where(['coupon_code' => $code, 'uid'=>$uid])->andWhere(['in', 'status', $status])->exists();
        
        if (!$shoporder){
            return true;
        }else {
            return false;
        }
    }
    
    /**
     * 参数处理
     * 2017年9月8日 上午11:19:03
     * @author liyee
     * @param unknown $parame
     * @param string $default
     */
    protected function getParam($param, $default = ''){
        $request = \Yii::$app->request;
        return empty($request->post($param))?$request->get($param, $default):$request->post($param, $default);
    }
    
    /**
     * 获取国家列表及默认国际
     * 2017年8月16日 上午10:05:38
     * @author liyee
     */
    public function actionCountries(){
        $countries = Region::countries();
        $region_name = array_column($countries, 'region_name');
        array_multisort($region_name, SORT_STRING, $countries);
        $default = \app\helpers\myhelper::payinfo('default');
        return $this->result(0, ['list'=> $countries, 'default' => $default], 'success');
    }
    
    /**
     * 获取支付方式
     * 2017年8月16日 上午11:13:03
     * @author liyee
     */
    public function actionPayChannel(){
        $shop_id = $this->getParam('shop_id', '2');
        if ($shop_id == 1){
            $appid = '7';
        }else {
            $appid = '7_2';
        }
        
        $payways = \app\helpers\myhelper::payinfo('payway', '', $appid);
        return $this->result(0, $payways, 'success');
    }
    
    /**
     * 获取国家和支付方式对应关系
     * 2017年8月16日 下午12:00:44
     * @author liyee
     */
    public function actionCountryToChannel(){
        $shop_id = $this->getParam('shop_id', '2');
        if ($shop_id == 1){
            $appid = 7;
        }else {
            $appid = '7_2';
        }
        
        $country_currency_payway = \app\helpers\myhelper::payinfo('country_currency_payway', '', $appid);
        return $this->result(0, $country_currency_payway, 'success');
    }
    
    /**
     * 根据sku_id获取商品详情
     * 2017年8月17日 下午4:52:44
     * @author liyee
     */
    public function actionProductInfo(){
        $items = $this->getParam('items');
        $shop_id = $this->getParam('shop_id', '2');

        $util = new ShopOrderUtil(json_decode($items, true), true, $shop_id);
        $util->calc();
        $items = $util->getItems();
        $items = GoodsSku::getSpecInfo($items);
        
        return $this->result(0, $items, 'success');
    }

    /**
     * 根据id获取地址信息
     * 2017年8月18日 下午1:41:31
     * @author liyee
     */
    public function addressInfo($id, $uid){
        $useraddress =  UserAddress::find()->where(['id'=>$id,'uid'=>$uid])->asArray()->one();
        return $useraddress;
    }
    
    /**
     * 创建订单
     * 2017年8月18日 上午11:26:41
     * @author liyee
     * @return number[]|string[]|unknown[]|number[][]|string[][]|unknown[][]|array[]|mixed[]
     */
    public function actionCreateOrder() {
        \Yii::$app->db_shop->enableSlaves = false;
        $shop_id = $this->getParam('shop_id', '2');
        $uid = $this->getParam('uid');
        $lang = $this->getParam('lang');
        $address_id = $this->getParam('address_id');
        $payment_country_id = $this->getParam('payment_country_id');
        $channel_id = $this->getParam('channel_id');
        $trans_type = $this->getParam('trans_type');
        $products = $this->getParam('products');
        $remark = $this->getParam('remark');
        $coupon_code = $this->getParam('coupon_code');
        $device = $this->getParam('device', 'pc');
        $products = htmlspecialchars_decode($products);
        $products = !is_array($products)?json_decode($products, true):$products;
        
        $addressInfo = $this->addressInfo($address_id, $uid);
        if ($addressInfo){
            if ($payment_country_id){
                if ($shop_id == 1){
                    $appid = '7';
                }else {
                    $appid = '7_2';
                }
                $payways = \app\helpers\myhelper::payinfo('payway', '', $appid);
                if (in_array($channel_id, array_keys($payways))){
                    if ($products && is_array($products)) {
                        if ($this->numberInterval($products)){
                            //订单基础信息，价格、数量、金额
                            $util = new ShopOrderUtil($products, true, $shop_id);
                            $items = $util->getItems();
                            foreach ($items as $item_id => $item) {
                                if (!isset($items[$item_id])) {
                                    return $this->result(1, [],'not exists item');
                                }
                                
                                if ($items[$item_id]['goods_status'] != 1 || $items[$item_id]['status'] != 1 ) {
                                    $name = $items[$item_id]['name'];
                                    return $this->result(1, [], "Goods $name has been off the shelves");
                                }
                                if ($items[$item_id]['store'] < $item['number']) {
                                    return $this->result(1, [],Yii::t('shop','shop.OutOfStock'));
                                }
                                if ($items[$item_id]['price'] <= 0) {
                                    return $this->result(1, [], 'price is err');
                                }
                                $items[$item_id]['number'] = $item['number'];
                                $items[$item_id]['categories'] = $products[$item_id]['categories'];
                                $items[$item_id]['amount'] = $item['number']*$item['price'];
                            }
                            
                            //优惠价格
                            $coupon_amount = 0.00;
                            if ($coupon_code){
                                $coupon_amount = $this->verifyByCode($coupon_code, $items, $uid);
                            }
                            
                            //创建订单 获得goods_id, goods_name, spec_id, spec_name, spec_value_id, spec_value
                            $util->calc();
                            $total = $util->getTotal();
                            $reality_amount = ($total-$coupon_amount)<0?0.00:($total-$coupon_amount);
                            
                            //运费
                            $transPrice =  GoodsTransPrice::find()->where(['country_id' => $addressInfo['country_id']])->one();
                            if (!$transPrice){
                                return $this->result(1, [], 'This country does not exist in the shipping list!');
                            }
                            $freight = $trans_type==1?$transPrice->price:$transPrice->price_urgent;
                            $promotion = GoodsPromotion::find()->where(['promotion_id' => 1])->one();
                            $promotion = json_decode($promotion->json, true);
                            if (($promotion['id'] == 1 || ($promotion['id'] == 2 && ($reality_amount >= $promotion['money']))) && ($trans_type == 1) ) {
                                $freight = 0.00;
                            }

                            //获取支付国家信息
                            $this->payment_country_id = $payment_country_id;
                            $countries = Region::countries();
                            $payment_country_info = array_filter($countries, function($v, $k) {
                                return $v['id'] == $this->payment_country_id;
                            }, ARRAY_FILTER_USE_BOTH);
                            $payment_country_info = array_values($payment_country_info); 
                            
                            $order = new ShopOrder();
                            $order->project_id = $appid;
                            $order->shop_id = (string)$shop_id;
                            $order->uid = (string)$uid;
                            $order->full_name = $addressInfo['fullname'];
                            $order->country = $addressInfo['country_id'];
                            $order->city = $addressInfo['city'];
                            $order->email = $addressInfo['email'];
                            $order->postal_code = $addressInfo['postal_code'];
                            $order->phone = $addressInfo['phone'];
                            $order->channel = '';
                            $order->channel_method = $channel_id;
                            $order->products = myhelper::product($util->getItems());
                            $order->amount = $total;
                            $order->freight = $freight;
                            $order->coupon_code = $coupon_code;
                            $order->coupon_amount = $coupon_amount;
                            $order->total_amount = $freight+$reality_amount;
                            $order->currency_id = '1';
                            $order->currency = 'USD';
                            $order->currency_symbol = '$';
                            $order->platform = $shop_id==1?'clothesforever':'lovecrunch';
                            $order->clientip = myhelper::get_client_ip();
                            $order->shipping_address_1 = $addressInfo['address'];
                            $order->shipping_address_2 = $addressInfo['address2'];
                            $order->remark = $remark;
                            $order->logistics_status = 11;
                            $order->istest = 'test';
                            $order->payment_country_code = $payment_country_info[0]['country_code'];
                            $order->payment_country_id = $payment_country_id;
                            $order->status          = 0;
                            $order->updatetime = $order->createtime = time();


                            //谷歌统计<<<

                            $gtm['option']  = isset($payways[$channel_id])?$payways[$channel_id]:'';
                            $product_gtm = $ids= [];
                            $num = 0;
                            foreach ($util->getItems() as $item){
                                $g = Goods::getGtm($item['goods_sku_id'],$item['number']);
                                $product_gtm[] = $g;
                                $num += $item['number'];
                                $ids[] = $g['id'];
                            }
                            $gtm['product_gtm'] = $product_gtm;
                            $gtm['orderid'] = 0;
                            $gtm['quantity'] = $num;
                            $gtm['country'] = LANG_SET;
                            $gtm['ids'] = implode(",",$ids);
                            $gtm['totalvalue'] = $order->total_amount;
                            $gtm['tax'] = '';
                            $gtm['shipping'] = $freight;
                            $gtm['coupon'] = $order->coupon_code;
                            $gtm['revenue'] = $order->total_amount;
                            //谷歌统计>>>



                            if ($order->save()){
                                $gtm['orderid'] = $order->id;

                                $url = $this->createPayurl($order, $device);
                                if ($url){
                                    $items_sku_id = array_keys($items);
                                    GoodsCart::deleteAll(['uid' => $uid, 'shop_id' => $shop_id, 'item_id' => $items_sku_id]);
                                    return $this->result(0, ['url' => $url,'gtm'=>$gtm], 'success');
                                }else {
                                    return $this->result(1, [], 'Failed to create pay address');
                                }
                            }else {
                                return $this->result(1, [], 'Data addition failed');
                            }
                        }else {
                            return $this->result(1, [], Yii::t('shop','ThisItemIsLimitedTo100PurchasesPerID'));
                        }
                    }else {
                        return $this->result(1, [], Yii::t('shop','CartIsEmpty'));
                    }
                }else {
                    return $this->result(1, [], 'Payment does not exist');
                }
            }else {
                return $this->result(1, [], 'Please choose which country you are paying for');
            }
        }else {
            return $this->result(1, [], 'The address is empty');
        }
    }
    
    /**
     * 优惠码
     * 2017年9月11日 下午3:53:51
     * @author liyee
     * @param unknown $coupon_code
     * @param unknown $items
     * @param unknown $uid
     * @return number|string
     */
    private function verifyByCode($coupon_code, $items, $uid){
        $uid = $uid;
        $code = $coupon_code;
        $shop_id = $this->getParam('shop_id', 2);
        
        $promotion_amount = 0.00;
        if ($code && $items) {
            if ($this->nunByuid($code, $uid)){
                if (!is_array($items)){
                    $items = json_decode($items, true);
                }
                $timestamp = time();
                
                $coupon = ShopCoupon::find()
                ->select(['category','promotion_type','allow_price','promotion_value'])
                ->where(['code' => $code,'status' => 1])
                ->andWhere(['<=','start_time',$timestamp])
                ->andWhere(['>=','end_time',$timestamp])
                ->andWhere(['>','use_times',0])
                ->one();
                
                if ($coupon) {
                    $category = json_decode($coupon->category, true); // 商品类别
                    $promotion_type = $coupon->promotion_type; // 优惠类型1:百分比，2：固定金额
                    $allow_price = $coupon->allow_price; // 满足金额
                    $promotion_value = $coupon->promotion_value; // 优惠额
                    $original_amount = 0.00;
                    
                    foreach ($items as $item){
                        $original_amount += $this->goodCoupon($item, $category);
                    }
                    
                    if ($original_amount >= $allow_price) {                        
                        switch ($promotion_type) {
                            case 2:
                                $promotion_amount = $promotion_value;
                                break;
                            default:
                                $promotion_amount = $original_amount * $promotion_value / 100;
                        }
                        if ($promotion_amount >= $original_amount){
                            $original_amount = $promotion_amount;
                        }
                        
                        $promotion_amount = number_format($promotion_amount, 2);
                    }
                } 
            }
        }
        
        return $promotion_amount;
    }
    
    /**
     * 判断物品的数量区间
     * 2017年8月16日 下午6:36:00
     * @author liyee
     */
    private function numberInterval($item){
        $continue = true;
        foreach ($item as $value) {
            $itemId = (int) $value['sku_id'];
            $number = (int) $value['number'];
            // todo
            if ($number <= 0 || $number > 100) {
                $continue = false;
            }
        }
        
        return $continue;
    }
    
    /**
     * 用户中心直接购买
     * 2017年8月21日 下午5:24:08
     * @author liyee
     * @param string $order_id
     */
    public function actionDirectBuy(){
        $order_id = $this->getParam('order_id', '2');
        $uid = $this->getParam('uid');
        $device = $this->getParam('device', 'pc');
        
        $orderInfo = ShopOrder::find()->where(['id'=>$order_id, 'uid'=>$uid])->one();
        if ($orderInfo){
            $url = $this->createPayurl($orderInfo, $device);
            if ($url){
                return $this->result(0, ['url' => $url], 'success');
            }else {
                return $this->result(1, [], 'Failed to create pay address');
            }
        }else {
            return $this->result(1, [], 'Order does not exist');
        }
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
     * 获取运费价格
     */
    public function actionFreight()
    {
        $start = microtime(true);
        $country = $this->getParam('country_id');
        $type = $this->getParam('type', 1);
        $amount = $this->getParam('amount');

        if (!$country || !$type || !$amount) {
            return $this->result(1, [], 'Parameters missing.');
        }

        $goodsTransPrice = GoodsTransPrice::find()
            ->select(['country_id', 'price as 1', 'price_urgent as 2'])
            ->where(['status' => GoodsTransPrice::STATUS_ENABLE])
            ->asArray()
            ->all();
        $goodsTransPrice = ArrayHelper::index($goodsTransPrice, 'country_id');

        if (isset($goodsTransPrice[$country][$type])) {
            $trans_price = $goodsTransPrice[$country][$type];
            $promotion = GoodsPromotion::find()->where(['promotion_id' => 1])->one();
            $promotion = Json::decode($promotion->json);
            if (($promotion['id'] == 1 && $type == 1) || ( $promotion['id'] == 2 && $type == 1 && $amount >= $promotion['money']) ) {
                $trans_price= 0;
            }

            $data = ['trans_price' => $trans_price];
            $end = microtime(true);
            \app\helpers\myhelper::inlog('order-new', 'freight', $end-$start);
            return $this->result(0, $data, 'Success');
        } elseif (isset($goodsTransPrice[235][$type])){
            $trans_price = $goodsTransPrice[235][$type];
            $promotion = GoodsPromotion::find()->where(['promotion_id' => 1])->one();
            $promotion = Json::decode($promotion->json);
            if (($promotion['id'] == 1 && $type == 1) || ( $promotion['id'] == 2 && $type == 1 && $amount >= $promotion['money']) ) {
                $trans_price= 0;
            }
            
            $data = ['trans_price' => $trans_price];
            return $this->result(0, $data, 'Success');
        } else {
            return $this->result(1, [], 'Freight template missing.');
        }
    }
    
    /**
     生成支付地址
     */
    private function createPayurl($orderInfo, $device = 'pc'){
        $shop_orderid = $orderInfo->id;
        $createtime = $orderInfo->createtime;
        if ($this->orderOvertime($createtime)){
            $currencyid = $orderInfo->currency_id;
            $uid = $orderInfo->uid;
            $freight = $orderInfo->freight;
            $amount = $orderInfo->amount;
            $total_amount = $orderInfo->total_amount;
            $coupon_amount = $orderInfo->coupon_amount;
            $channel_method = $orderInfo->channel_method;
            $payment_country_code = $orderInfo->payment_country_code;
            $currency = !empty($orderInfo->currency)?$orderInfo->currency:'USD';
            $symbol = !empty($orderInfo->currency_symbol)?$orderInfo->currency_symbol:'$';
            $lang = LANG_SET;
            $l = isset(\Yii::$app->params['language'][$lang])?\Yii::$app->params['language'][$lang]:1;
            
            $key = \Yii::$app->params['TOKEN']['shop'];
            $ShopPay = \Yii::$app->params['MY_URL']['ShopPay_2'];
            $des = new DES();
            $des->setKey($key);
            $des->iv = $key;
            
            $staptamp = microtime(true);
            $time = floor($staptamp*1000);
            $show_total = ($amount-$coupon_amount)<0?0.00:($amount-$coupon_amount);
            $actual_amount =  $show_total+$freight;
            $project_id = $orderInfo->project_id;
            $plaintext_test = "countrycode=$payment_country_code&channelwayid=$channel_method&packid=$shop_orderid&currencyid=$currencyid&gameid=$project_id&userid=$uid&l=$l&amount=$actual_amount&currency=$currency&symbol=$symbol&time=$time";
            $url = $ShopPay.'/v1/shoppay/start?device='.$device.'&sign='.urlencode(base64_encode($des->encrypt($plaintext_test)));
            
            return $url;
        }else {
            return false;   
        }
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
    
    
    /**
     * Lists all GoodsPromotion models.
     * @return mixed
     */
    public function actionPromotion(){
        $searchModel  = new GoodsPromotionSearch();
        $dataProvider = $searchModel->search(ArrayHelper::merge(Yii::$app->request->queryParams, [$searchModel->formName() => [
                'status' => GoodsPromotion::STATUS_ENABLE,
        ]]), 1);
        $pageSize                           = Yii::$app->request->get($dataProvider->Pagination->pageSizeParam);
        $dataProvider->Pagination->pageSize = $pageSize ? $pageSize : $dataProvider->Pagination->pageSize;
        
        $returnData = [
                'data' => [],
        ];
        foreach ($dataProvider->getModels() as $model) {
            $returnData['data'] = $model->toArray();
        }
        
        if (isset($returnData['data']['json'])){
            $returnData['data'] = json_decode($returnData['data']['json']);
        }
        
        $returnData['page'] = [
                'link'       => $dataProvider->Pagination->getLinks(),
                'totalCount' => $dataProvider->Pagination->totalCount,
                'pageSize'   => $dataProvider->Pagination->getPageSize(),
                'pageCount'  => $dataProvider->Pagination->getPageCount(),
        ];
        return $this->result(0,$returnData,'success');
    }
    
    /**
     * 汇率货币列表
     * 2017年4月25日 下午4:28:09
     * @author liyee
     */
    public function actionCurrencyList() {
        $exchange_rate_id = $this->getParam('exchange_rate_id');
        
        $result = \app\helpers\myhelper::ccurrcyList($exchange_rate_id);
        
        return $this->result(0, $result, 'success');
    }
    
    /**
     * 订单列表接口
     * 2017年8月28日 下午4:03:44
     * @author liyee
     */
    public function actionList()
    {
        $start = microtime(true);
        $uid      = $this->getParam('uid');
        $shop_id  = $this->getParam('shop_id', '2');
        $lang = $this->getParam('lang', 'en_us');
        $page     = $this->getParam('page', 1);
        $pageSize = $this->getParam('per-page', 10);

        $list = ShopOrder::listinfo($uid, $shop_id, $page, $pageSize, $lang);
        
        $end = microtime(true);
        \app\helpers\myhelper::inlog('order-new', 'list-1', $end-$start);
        
        return $this->result(0, $list, 'success');
    }
    
    /**
     * 优惠码功能
     * 2017年9月4日 上午11:55:43
     * @author liyee
     */
    public function actionVerifyByCode(){
        $uid = $this->getParam('uid');
        $code = $this->getParam('code');
        $categories = $this->getParam('categories');
        $shop_id = $this->getParam('shop_id', 2);
        $type = $this->getParam('type', '1');
        
        
        if ($code && $categories) {
            if ($this->nunByuid($code, $uid)){
                if (!is_array($categories)){
                    $categories = json_decode($categories, true);
                }
                $timestamp = time();
                
                $coupon = ShopCoupon::find()
                ->select(['category','promotion_type','allow_price','promotion_value'])
                ->where(['code' => $code,'status' => 1])
                ->andWhere(['<=','start_time',$timestamp])
                ->andWhere(['>=','end_time',$timestamp])
                ->andWhere(['>','use_times',0])
                ->one();
                
                if ($coupon) {
                    $category = json_decode($coupon->category, true); // 商品类别
                    $promotion_type = $coupon->promotion_type; // 优惠类型1:百分比，2：固定金额
                    $allow_price = $coupon->allow_price; // 满足金额
                    $promotion_value = $coupon->promotion_value; // 优惠额
                    $original_amount = 0.00;
                    
                    foreach ($categories as $item){
                        $original_amount += $this->goodCoupon($item, $category);
                    }
                    
                    if ($original_amount >= $allow_price) {
                        $promotion_amount = 0.00;
                        switch ($promotion_type) {
                            case 2:
                                $promotion_amount = $promotion_value;
                                break;
                            default:
                                $promotion_amount = $original_amount * $promotion_value / 100;
                        }
                        if ($promotion_amount >= $original_amount){
                            $original_amount = $promotion_amount;
                        }
                        
                        $promotion_amount = number_format($promotion_amount, 2);
                        //                         $this->toOrderTemp($from_id, $code, $promotion_amount);
                        return $this->result(0, ['amount'=>$promotion_amount], 'success');
                    } else {
                        return $this->result(1004, [],'Code does not exist!');
                    }
                } else {
                    return $this->result(1003, [], 'Code does not exist!');
                }
            }else {
                return $this->result(1002,  [],'Code does not exist!');
            }
        } else {
            return $this->result(1001, [],'Code does not exist!');
        }        
    }
    
    /**
     * 针对每个物品返回优惠金额
     * 2017年5月24日 下午8:58:18
     * @author liyee
     */
    protected function goodCoupon($good, $category){
        $amount = 0.00;
        $categories = $good['categories'];
        $categories = is_array($categories)?$categories:explode(',', $categories);
        $intersect = array_intersect($categories, $category);
        if ($intersect){
            $amount = $good['amount'];
        }
        
        return $amount;
    }
    
    /**
     * 获取订单详情
     * 2017年9月7日 下午3:41:16
     * @author liyee
     */
    public function actionOrderInfo(){
        $shop_id = $this->getParam('shop_id', '2');
        $uid = $this->getParam('uid');
        $orderid = $this->getParam('orderid');
        
        $key = \Yii::$app->params['TOKEN']['projectKey_'.$shop_id];
        if ($orderid = (myhelper::DesDecryptNew($orderid, $key))){
            $data = [];
            $order = ShopOrder::find()->select(['id','products','coupon_code','freight','total_amount'])->where(['id'=>$orderid, 'uid'=>$uid, 'shop_id'=>$shop_id])->one();
            if (!$order){
                return $this->result(1, $data, 'Order does not exist');
            }
            $products = json_decode($order->products, true);
            
//             $gtm = [];
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
                
//                 $GTMINFO['name'] = $productValue['name'];
//                 $GTMINFO['id'] = $productValue['goods_sku_id'];
//                 $GTMINFO['price'] = $productValue['price'];
//                 $GTMINFO['brand'] = $brand?$brand->brand_name:'';
//                 $GTMINFO['category'] = $category_info?$category_info->name:'';
//                 $GTMINFO['variant'] = $varient;
//                 $GTMINFO['quantity'] = $productValue['number'];
                
//                 $gtm[] = (object)$GTMINFO;
            }
            
            $data['total_amount'] = $order->total_amount;
            $data['orderid'] = $orderid;
            $data['coupon_code'] = $order->coupon_code;
            $data['freight'] = $order->freight;
//             $data['gtm'] = $gtm;
            
            return $this->result(0, $data, 'success');
        }
        
    }
    
    /**
     * 更新订单状态
     * 2017年8月14日 上午11:53:52
     * @author liyee
     */
    public function actionOrderToStatus(){
        $code = 1;
        $uid = $this->getParam('uid');
        $id = $this->getParam('orderid');
        $shopOrder = ShopOrder::find()->where(['id'=>$id, 'uid'=>$uid])->one();
        if ($shopOrder){
            $status = $shopOrder->status;
            if ($status == 1){
                $code = 0;
            }elseif ($status == 0){
                $shopOrder->status = 3;
                if ($shopOrder->save()){
                    $code = 0;
                }
            }
        }
        
        return $this->result($code);
    }
    
}
