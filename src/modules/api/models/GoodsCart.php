<?php

namespace app\modules\api\models;

use Yii;

/**
 * This is the model class for table "cart".
 *
 * @property integer $id
 * @property integer $shop_id
 * @property integer $uid
 * @property integer $item_id
 * @property double $price
 * @property integer $number
 * @property integer $created_time
 * @property integer $updated_time
 */
class GoodsCart extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'goods_cart';
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
            [['shop_id', 'uid', 'item_id', 'price', 'number'], 'required'],
            [['shop_id', 'uid', 'item_id', 'number', 'created_time', 'updated_time'], 'integer'],
            [['price'], 'number']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'shop_id' => Yii::t('app', 'Shop ID'),
            'uid' => Yii::t('app', 'Uid'),
            'item_id' => Yii::t('app', 'Goods Item ID'),
            'price' => Yii::t('app', 'Goods Item Price'),
            'number' => Yii::t('app', 'Goods Number'),
            'created_time' => Yii::t('app', 'Created Time'),
            'updated_time' => Yii::t('app', 'Updated Time'),
        ];
    }
}
