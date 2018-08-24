<?php
namespace app\modules\shop\controllers;
use Yii;
use \app\helpers\myhelper;
use \app\modules\shop\models\ShopOrder;
use \app\modules\shop\models\GoodsPromotion;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use app\modules\shop\models\GoodsPromotionSearch;
use app\Library\ShopPay\ShopPay;

class OrderController extends OCommonController
{
    public $defaultAction = 'index';
    public $payment_country_id;
    
    public function init()
    {
        parent::init();
        $this->enableCsrfValidation = false;
        if(!$this->is_login){
            return $this->result(10001,[],'please log in first!');
        }
    }
    
    /**
     * 获取国家列表及默认国际
     * 2017年8月16日 上午10:05:38
     * @author liyee
     */
    public function actionCountries(){
        $shopPay = new ShopPay('2');
        $data = $shopPay->adap('shop', 'countries');
        $data = !is_array($data)?json_decode($data, true):$data;
        
        return $this->resultNew($data);
    }
    
    /**
     * 获取支付方式
     * 2017年8月16日 上午11:13:03
     * @author liyee
     */
    public function actionPayChannel(){
        $shopPay = new ShopPay('2');
        $data = $shopPay->adap('shop', 'pay-channel');
        $data = !is_array($data)?json_decode($data, true):$data;
        
        return $this->resultNew($data);
    }
    
    /**
     * 获取国家和支付方式对应关系
     * 2017年8月16日 下午12:00:44
     * @author liyee
     */
    public function actionCountryToChannel(){
        $shopPay = new ShopPay('2');
        $data = $shopPay->adap('shop', 'country-to-channel');
        $data = !is_array($data)?json_decode($data, true):$data;
        
        return $this->resultNew($data);
    }
    
    /**
     * 根据sku_id获取商品详情
     * 2017年8月17日 下午4:52:44
     * @author liyee
     */
    public function actionProductInfo(){
        $request = \Yii::$app->request;
        $items = $request->get('items');

        $shopPay = new ShopPay('2');
        $data = $shopPay->adap('shop', 'product-info', [
            'items' => $items
        ]);
        $data = !is_array($data)?json_decode($data, true):$data;
        
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
        $params = [
            'uid' => $this->getUid(),
            'lang' => $this->getLang(),
            'address_id' => $request->get('address_id'),
            'payment_country_id' => $request->get('payment_country_id'),
            'channel_id' => $request->get('channel_id'),
            'trans_type' => $request->get('trans_type'),
            'products' => $request->get('products'),
            'remark' => $request->get('remark'),
            'coupon_code' => $request->get('coupon_code', ''),
        ];
        
        $shopPay = new ShopPay('2');
        $data = $shopPay->adap('shop', 'create-order', $params);
        $data = !is_array($data)?json_decode($data, true):$data;
        
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
        $order_id = $request->get('order_id');
        $uid = $this->getUid();
        
        $shopPay = new ShopPay('2');
        $data = $shopPay->adap('pay', 'direct-buy', [
            'order_id' => $order_id,
            'uid' => $uid,
        ]);
        
        echo $data;
    }

    /**
     * 获取运费价格
     */
    public function actionFreight()
    {
        $country = Yii::$app->request->get('country_id');
        $type = Yii::$app->request->get('type');
        $amount = Yii::$app->request->get('amount');

        $shopPay = new ShopPay('2');
        $data = $shopPay->adap('shop', 'freight', [
            'country_id' => $country,
            'type' => $type,
            'amount' => $amount
        ]);
        $data = !is_array($data)?json_decode($data, true):$data;
        
        return $this->resultNew($data);
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
        $request = \Yii::$app->request;
        $exchange_rate_id = $request->get('exchange_rate_id', '');
        
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
        $uid = $this->getUid();
        $shop_id = $this->getShopId();
        $page     = Yii::$app->request->get('page', 1);
        $pageSize = Yii::$app->request->get('per-page', 1);

        $shoppay = new ShopPay($shop_id);
        $data = $shoppay->listInfo($uid, $page = 1, $pageSize = 10);
        $data = !is_array($data)?json_decode($data, true):$data;
        
        return $this->resultNew($data);
    }
    
    /**
     * 优惠码功能
     * 2017年9月4日 上午11:55:43
     * @author liyee
     */
    public function actionVerifyByCode(){
        $request = \Yii::$app->request;
        $uid = $request->get('uid');
        $code = $request->get('code');
        $categories = $request->get('categories');
        $shop_id = $request->get('shop_id');
        $type = $request->get('type', 1);
        
        $shoppay = new ShopPay($shop_id);
        $data = $shoppay->verifyByCode($uid, $code, $categories, $type);
        $data = !is_array($data)?json_decode($data, true):$data;
        
        return $this->resultNew($data);
    }

    /**
     * 获取用户id
     *
     * @return int
     */
    private function getUid()
    {
        return $this->user_info['id'];
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

    private function err($code, $message) {
        return ['code' => $code, 'message' => $message, 'data' => []];
    }

    private function err_page($code, $message) {
        return ['code' => $code, 'message' => $message, 'data' => []];
    }
    
}
