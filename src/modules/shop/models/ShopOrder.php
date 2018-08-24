<?php

namespace app\modules\shop\models;

use Yii;
use yii\data\Pagination;
use yii\caching\DbDependency;
use app\helpers\myhelper;
use app\modules\shop\services\Goods;

/**
 * This is the model class for table "shop_order".
 *
 * @property integer $id
 * @property integer $from_id
 * @property string $project_id
 * @property string $uid
 * @property string $full_name
 * @property string $country
 * @property string $city
 * @property string $email
 * @property string $postal_code
 * @property string $phone
 * @property string $oid
 * @property string $orderid
 * @property string $channel_orderid
 * @property string $channel
 * @property string $channel_method
 * @property string $products
 * @property string $amount
 * @property string $freight
 * @property string $coupon_code
 * @property string $coupon_amount
 * @property string $total_amount
 * @property string $currency_id
 * @property string $currency
 * @property string $currency_symbol
 * @property string $refund
 * @property string $platform
 * @property string $clientip
 * @property string $shipping_address_1
 * @property string $shipping_address_2
 * @property string $logistics_information
 * @property string $logistics_number
 * @property string $remark
 * @property string $remark2
 * @property integer $logistics_status
 * @property string $payment_country_code
 * @property integer $payment_country_id
 * @property string $istest
 * @property integer $status
 * @property integer $updatetime
 * @property integer $createtime
 * @property string $shop_id
 * @property integer $step
 */
class ShopOrder extends \yii\db\ActiveRecord
{
    public static $uid;
    public static $shop_id;
    public static $appid;
    public static $lang;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_order';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_shop');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['from_id', 'logistics_status', 'payment_country_id', 'status', 'updatetime', 'createtime', 'shop_id', 'step'], 'integer'],
            [['products'], 'string'],
            [['amount', 'freight', 'coupon_amount', 'total_amount'], 'number'],
            [['project_id', 'full_name', 'remark2'], 'string', 'max' => 255],
            [['uid', 'phone', 'oid', 'orderid', 'channel_orderid', 'channel', 'channel_method', 'coupon_code', 'currency_id', 'refund', 'platform', 'clientip', 'istest'], 'string', 'max' => 64],
            [['country', 'city', 'email', 'postal_code'], 'string', 'max' => 128],
            [['currency', 'currency_symbol'], 'string', 'max' => 8],
            [['shipping_address_1', 'shipping_address_2', 'logistics_information', 'logistics_number', 'remark'], 'string', 'max' => 256],
            [['payment_country_code'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'from_id' => 'From ID',
            'project_id' => 'Project ID',
            'uid' => 'Uid',
            'full_name' => 'Full Name',
            'country' => 'Country',
            'city' => 'City',
            'email' => 'Email',
            'postal_code' => 'Postal Code',
            'phone' => 'Phone',
            'oid' => 'Oid',
            'orderid' => 'Orderid',
            'channel_orderid' => 'Channel Orderid',
            'channel' => 'Channel',
            'channel_method' => 'Channel Method',
            'products' => 'Products',
            'amount' => 'Amount',
            'freight' => 'Freight',
            'coupon_code' => 'Coupon Code',
            'coupon_amount' => 'Coupon Amount',
            'total_amount' => 'Total Amount',
            'currency_id' => 'Currency ID',
            'currency' => 'Currency',
            'currency_symbol' => 'Currency Symbol',
            'refund' => 'Refund',
            'platform' => 'Platform',
            'clientip' => 'Clientip',
            'shipping_address_1' => 'Shipping Address 1',
            'shipping_address_2' => 'Shipping Address 2',
            'logistics_information' => 'Logistics Information',
            'logistics_number' => 'Logistics Number',
            'remark' => 'Remark',
            'remark2' => 'Remark2',
            'logistics_status' => 'Logistics Status',
            'payment_country_code' => 'Payment Country Code',
            'payment_country_id' => 'Payment Country ID',
            'istest' => 'Istest',
            'status' => 'Status',
            'updatetime' => 'Updatetime',
            'createtime' => 'Createtime',
            'shop_id' => 'Shop ID',
            'step' => 'Step',
        ];
    }
    
    /**
     * 订单分页数据(根据用户)
     * 2017年8月28日 下午5:44:09
     * @author liyee
     * @param unknown $uid
     * @param unknown $shopid
     * @return \app\modules\shop\models\Pagination[]|array[]|\yii\db\ActiveRecord[][]
     */
    public static function listinfo($uid=0, $shop_id='', $page = 1, $pageSize = 1, $lang){
        self::$uid = $uid;
        self::$shop_id = $shop_id;
        self::$appid = $shop_id==1?'7':'7_2';
        self::$lang = $lang;
        
        $sql = 'select updatetime from shop_order where uid='.$uid.' and shop_id=\''.$shop_id.'\' order by updatetime desc limit 1';
        $dependency = new DbDependency(['db' => 'db_shop', 'sql' => $sql]);
        $result = ShopOrder::getDb()->cache(function ($db) use($page, $pageSize) {
            $query = ShopOrder::find()->select(['id','uid','products','country','city','shipping_address_1','shipping_address_2','channel_method','amount','freight','coupon_amount','total_amount','createtime', 'status', 'logistics_status', 'full_name', 'phone', 'logistics_information', 'logistics_number'])->where(['uid'=>self::$uid, 'shop_id'=>self::$shop_id])->orderBy(['id'=>SORT_DESC]);
            $count = $query->count();
            $pagination = new Pagination(['totalCount'=>$count,  'page' => $page-1, 'pageSize' => $pageSize]);
            $shop_order = $query->offset($pagination->offset)->limit($pagination->limit)->all();
            $data = [];
            $countries = Region::countriesNew();
            foreach ($shop_order as $item){
                $shipping_address = !empty($item['shipping_address_1'])?$item['shipping_address_1']:$item['shipping_address_2'];
                $products = self::products(json_decode($item['products'], true), self::$shop_id, self::$lang);
                $data[] = [
                    'id' => $item['id'],
                    'uid' => $item['uid'],
                    'address' => $shipping_address.','.$item['city'].','.$countries[$item['country']]['region_name'],
                    'channel_method' => myhelper::payinfo('payway', $item['channel_method'], self::$appid),
                    'products' => json_decode($products, true),
                    'amount' => $item['amount'],
                    'freight' => $item['freight'],
                    'coupon_amount' => $item['coupon_amount'],
                    'total_amount' => $item['total_amount'],
                    'createtime'   => $item['createtime'],
                    'status'       => $item['status'],
                    'logistics_status' => $item['logistics_status'],
                    'full_name'    => $item['full_name'],
                    'phone'        => $item['phone'],
                    'logistics_information' => $item['logistics_information'],
                    'logistics_number' => $item['logistics_number'],
                ];  
            } 
            
            return ['models'=>$data,'pages'=>$pagination];
        }, 60, $dependency);
        
        return $result;
    }
    
    /**
     * 解析商品信息
     * 2017年8月29日 上午11:56:48
     * @author liyee
     */
    public static function products($products, $shop_id, $lang){
        $data = [];
        if ($products){
            $sku_ids = array_column($products, 'goods_sku_id');
            $info = Goods::products($sku_ids, $shop_id, $lang);
            foreach ($info as &$p){
                if(!isset($p['goods_sku_id'])) continue;
                $goods_sku_id = $p['goods_sku_id'];
                $sku_info = GoodsSku::findOne($p['goods_sku_id']);
                if(empty($sku_info)) continue;
                $p = array_merge($products[$goods_sku_id], $p);
                
                $data = $info;
            }
        }
        return json_encode($data);
    }
}
