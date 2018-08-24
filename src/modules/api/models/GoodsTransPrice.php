<?php

namespace app\modules\api\models;

use Yii;

/**
 * This is the model class for table "goods_trans_price".
 *
 * @property string $id
 * @property string $country_id
 * @property string $country
 * @property double $price
 * @property double $price_urgent 
 * @property string $currency
 * @property integer $status
 * @property string $created_time
 * @property string $updated_time
 *
 * @property Region $country0
 */
class GoodsTransPrice extends \yii\db\ActiveRecord {
    const STATUS_DISABLE = 0;
    const STATUS_ENABLE  = 1;

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb() {
        return Yii::$app->get('db_shop');
    }

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'goods_trans_price';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['country_id'], 'required'],
            [['country_id', 'status', 'created_time', 'updated_time'], 'integer'],
            [['price', 'price_urgent'], 'number'],
            [['country'], 'string', 'max' => 255],
            [['currency'], 'string', 'max' => 32],
        ];
    }

    public function fields() {
        return [
            'id'        => 'id',
            'countryID' => 'country_id',
            'country'   => function ($model) {
                return $model->countryItem->region_name;
            },
            'price'     => 'price',
            'priceUrgent'     => 'price_urgent',
            'currency'  => 'currency',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCountryItem() {
        return $this->hasOne(Region::className(), ['id' => 'country_id']);
    }
}
