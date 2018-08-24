<?php

namespace app\modules\shop\models;

use Yii;
use yii\caching\DbDependency;

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

    /**
     * Lists all TransPrice models
     * @return mixed
     */
    public static function countryInfo()
    {
        $sql = sprintf('SELECT `updated_time` FROM %s ORDER BY `updated_time` DESC LIMIT 1', self::tableName());
        $dbDependency = new DbDependency(['db' => 'db_shop', 'sql' => $sql]);
        $result = self::getDb()->cache(function($db) {
            $countries = self::find()->with('countryItem')->where(['status' => self::STATUS_ENABLE])->all();
            return array_map(function ($country){
                return  $country->toArray();
            }, $countries);
        }, 60, $dbDependency);
        return $result;
    }
}
