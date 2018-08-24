<?php

namespace app\modules\shop\models;

use Yii;

/**
 * This is the model class for table "shop_order_product".
 *
 * @property integer $id
 * @property integer $order_id
 * @property string $name
 * @property string $good_id
 * @property string $sku_id
 * @property double $price
 * @property double $price_discounted
 * @property string $coupon_discount
 * @property string $currency
 * @property integer $num
 * @property string $color
 * @property string $size
 * @property integer $status
 * @property integer $updatetime
 * @property integer $createtime
 */
class ShopOrderProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_order_product';
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
            [['order_id', 'num', 'status', 'updatetime', 'createtime'], 'integer'],
            [['price', 'price_discounted'], 'number'],
            [['name'], 'string', 'max' => 255],
            [['good_id', 'sku_id'], 'string', 'max' => 64],
            [['coupon_discount'], 'string', 'max' => 128],
            [['currency', 'color', 'size'], 'string', 'max' => 16],
            [['order_id', 'good_id', 'sku_id'], 'unique', 'targetAttribute' => ['order_id', 'good_id', 'sku_id'], 'message' => 'The combination of Order ID, Good ID and Sku ID has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Order ID',
            'name' => 'Name',
            'good_id' => 'Good ID',
            'sku_id' => 'Sku ID',
            'price' => 'Price',
            'price_discounted' => 'Price Discounted',
            'coupon_discount' => 'Coupon Discount',
            'currency' => 'Currency',
            'num' => 'Num',
            'color' => 'Color',
            'size' => 'Size',
            'status' => 'Status',
            'updatetime' => 'Updatetime',
            'createtime' => 'Createtime',
        ];
    }
}
