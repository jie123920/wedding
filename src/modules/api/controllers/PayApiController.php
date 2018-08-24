<?php

namespace app\modules\api\controllers;

use app\modules\api\models\ShopAbnormal;
use app\modules\api\models\ShopOrder;
use app\helpers\myhelper;
use app\Library\curl\Curl;
use app\modules\api\models\ShopCoupon;
use app\helpers\sendEmail;
use app\modules\api\models\Goods;
use app\modules\api\models\GoodsSku;
use app\modules\api\models\GoodsCategory;

class PayApiController extends ExtendsController
{
    public function init() {
        parent::init();
        parent::behaviors();
    }

    public function actionBack(){
        $request = \Yii::$app->request;
        $name = $request->get('name');
        $content = $request->get('content');
        $content = json_decode($content, true);

        if ($orderid = (myhelper::DesDecrypt($name))){
            switch ($content['status']){
                case 1:
                    $this->Normal($orderid, $content);
                    break;
                case 2:
                    $this->Abnormal($orderid, $content);
                    break;
                case 3:
                    $this->Abnormal($orderid, $content);
                    break;
                default:                    
            }
            
            echo "OK";
        }        
    }
    
    /**
     * 订单正常流程
     * 2017年2月27日 下午3:31:19
     * @author liyee
     * @param unknown $content
     */
    private function Normal($orderid, $content){
        $id = $content['shop_orderid'];
        $time = time();
        $shopOrder = ShopOrder::findOne($id);
        if ($shopOrder){
            $status = $shopOrder->status;
            if ($status != 1){
                $shopOrder->orderid = $orderid;
                $shopOrder->istest = $content['istest'];
                $shopOrder->status = $content['status'];
                $shopOrder->updatetime = $time;
                
                if ($shopOrder->save()){
                    $coupon_code = $shopOrder->coupon_code;
                    $id = $shopOrder->id;
                    $email = $shopOrder->email;
                    $products = $shopOrder->products;
                    $freight = $shopOrder->freight;
                    $total_amount = $shopOrder->total_amount;
                    $project_id = $shopOrder->project_id;
                    $send_email = sendEmail::run('order_seccess', ['orderid'=>$id, 'email'=>$email, 'products'=>$products, 'freight'=>$freight, 'total_amount'=>$total_amount, 'project_id'=>$project_id]);
                    if ($coupon_code){
                        $this->removeCoupon($coupon_code);
                    }
                }
            }
        }else{
            echo 'NO';
            exit();
        }               
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
     * 异常订单处理
     * 2017年2月27日 下午3:07:38
     * @author liyee
     * @param unknown $orderid
     * @param unknown $content
     */
    private function Abnormal($orderid, $content) {
        $time = time();
        if (!ShopAbnormal::find()->where(['orderid' => $orderid])->one()){
            $shopabnormal = new ShopAbnormal();
        
            $shopabnormal->shop_orderid = $orderid;
            $shopabnormal->project_id = $content['project_id'];
            $shopabnormal->source = $content['source'];
            $shopabnormal->uid = $content['userid'];
            $shopabnormal->orderid = $orderid;
            $shopabnormal->channel = $content['channel'];
            $shopabnormal->amount = $content['amount'];
            $shopabnormal->currency = $content['currency'];
            $shopabnormal->istest = $content['istest'];
            $shopabnormal->status = $content['status'];
            $shopabnormal->updatetime = $time;
            $shopabnormal->createtime = $time;
             
            $shopabnormal->save();
        }else {
            $shopabnormal = ShopAbnormal::find()->where(['orderid'=>$orderid])->one();
            $shopabnormal->status = $content['status'];
            $shopabnormal->updatetime = $time;
            $shopabnormal->save();
        }
    }
    
    /**
     * 汇率货币列表
     * 2017年4月25日 下午4:28:09
     * @author liyee
     */
    public function actionCurrencyList() {
        $request = \Yii::$app->request;
        $exchange_rate_id = $request->get('exchange_rate_id', '');
        
        $result = \app\modules\wedding\services\Region::GetIdByIp($exchange_rate_id,myhelper::get_client_ip());
        
        $this->dataOut(0, $result);
    }
    
    public function actionLoad() {
        $list = myhelper::ccurrcyList();
        
//         echo $list['default']['country_code'].','.$list['default']['symbol'].$list['default']['name'];die;
        var_dump($list);die;
        var_dump(array_column($list, 'country_id', 'id'));die;
        foreach ($list as $child){
            echo $child['country_code'].','.$child['symbol'].$child['name'];
        }
        
        die;
        
        $url = 'http://finance.yahoo.com/webservice/v1/symbols/allcurrencies/quote';
        $curl = new Curl();
        $data = $curl->setOptions([
            CURLOPT_TIMEOUT => 30
        ])->get($url);

        if ($data){
            $data = simplexml_load_string($data);
            $list = $data->resources->resource;
            foreach ($list as $child){
                list($name, $price, $symbol, $ts, $type, $utctime, $volume) = $child->field;
                echo $name;die;
            }
        }
    }
    
    public function actionGoogleTag(){
        $request = \Yii::$app->request;
        $orderid = $request->get('orderid', '');
        $key = \Yii::$app->params['TOKEN']['projectKey'];
        if ($orderid = (myhelper::DesDecryptNew($orderid, $key))){
            $data = [];
            $order = ShopOrder::find()->select(['id','products','coupon_code','freight','total_amount'])->where(['id'=>$orderid])->one();
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
            
            $data['total_amount'] = $order->total_amount;
            $data['orderid'] = $orderid;
            $data['coupon_code'] = $order->coupon_code;
            $data['freight'] = $order->freight;
            $data['gtm'] = $gtm;
            
            echo myhelper::DesEncryptNew(json_encode($data), $key);
        }
    }
    
    /**
     * 根据用户信息查询订单列表
     * 2017年8月2日 下午2:34:24
     * @author liyee
     */
    public function actionOrderList(){
        $list = [];
        $request = \Yii::$app->request;
        $uid = $request->get('uid');
        $platform = $request->get('platform');
        $limit = 10;
        $offset = 0;
        if ($uid && $platform){
            $list = ShopOrder::find()->select(['id'])->where(['uid'=>$uid,'platform'=>$platform])->orderBy(['id'=>SORT_DESC])->limit($limit)->offset($offset)->asArray()->all();
            
        }        
    }


    public function actionDefaultCurrency() {
        $request = \Yii::$app->request;
        $exchange_rate_id = $request->get('exchange_rate_id', '');

        $result = \app\modules\wedding\services\Region::GetIdByIp($exchange_rate_id,myhelper::get_client_ip());

        $this->dataOut(0, $result['default']);
    }
}
