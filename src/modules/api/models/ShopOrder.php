<?php

namespace app\modules\api\models;

use Yii;

/**
 * This is the model class for table "shop_order".
 *
 * @property integer $id
 * @property integer $from_id
 * @property string $project_id
 * @property string $uid
 * @property string $full_name
 * @property string $country
 * @property string $country_code
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
 * @property string $remark
 * @property integer $logistics_status
 * @property string $istest
 * @property integer $status
 * @property integer $updatetime
 * @property integer $createtime
 * @property string $shop_id
 * @property integer $step
 */
class ShopOrder extends \yii\db\ActiveRecord
{
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
            [['from_id', 'logistics_status', 'status', 'updatetime', 'createtime', 'shop_id', 'step'], 'integer'],
            [['products', 'logistics_information'], 'string'],
            [['project_id', 'full_name'], 'string', 'max' => 255],
            [['uid', 'phone', 'oid', 'orderid', 'channel_orderid', 'channel', 'channel_method', 'amount', 'freight', 'total_amount', 'currency_id', 'refund', 'platform', 'clientip', 'istest'], 'string', 'max' => 64],
            [['country', 'city', 'email', 'postal_code'], 'string', 'max' => 128],
            [['country_code'], 'string', 'max' => 32],
            [['currency', 'currency_symbol'], 'string', 'max' => 8],
            [['shipping_address_1', 'shipping_address_2', 'remark'], 'string', 'max' => 256]
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
            'country_code' => 'Country Code',
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
            'remark' => 'Remark',
            'logistics_status' => 'Logistics Status',
            'istest' => 'Istest',
            'status' => 'Status',
            'updatetime' => 'Updatetime',
            'createtime' => 'Createtime',
            'shop_id' => 'Shop ID',
            'step' => 'Step',
        ];
    }
}
