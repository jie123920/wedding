<?php

namespace app\modules\shop\models;

use Yii;

/**
 * This is the model class for table "shop_order_temp".
 *
 * @property integer $id
 * @property string $shop_id
 * @property string $uid
 * @property string $fullname
 * @property integer $country_id
 * @property string $country
 * @property string $city
 * @property string $email
 * @property string $postal_code
 * @property string $phone
 * @property string $channel
 * @property string $cahnnel_method
 * @property string $products
 * @property string $amount
 * @property string $freight
 * @property string $coupon_code 
 * @property string $coupon_amount 
 * @property string $toal_amount
 * @property string $currency
 * @property string $currency_symbol
 * @property string $platform
 * @property string $clientip
 * @property string $address
 * @property string $address2
 * @property integer $status
 * @property integer $trans_type
 * @property integer $step
 * @property integer $from_cart
 * @property integer $payment_country_id
 * @property integer $payment_channel_id
 * @property integer $payment_currency_id
 * @property double $total_amount
 */
class ShopOrderTemp extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_order_temp';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_shop');
    }

}
