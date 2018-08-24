<?php

namespace app\modules\api\models;

use Yii;

/**
 * This is the model class for table "region".
 *
 * @property string $id
 * @property string $region_name
 * @property string $country_code
 * @property string $area_code
 * @property string $name_zh
 * @property string $pid
 *
 * @property GoodsTransPrice[] $goodsTransPrices
 */
class Region extends \yii\db\ActiveRecord {
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
        return 'region';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['region_name', 'area_code', 'name_zh'], 'required'],
            [['pid'], 'integer'],
            [['region_name', 'name_zh'], 'string', 'max' => 40],
            [['country_code'], 'string', 'max' => 20],
            [['area_code'], 'string', 'max' => 5],
        ];
    }

    public function fields() {
        return [
            'id'          => 'id',
            'regionName'  => 'region_name',
            'countryCode' => 'country_code',
            'areaCode'    => 'area_code',
            'pid'         => 'pid',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGoodsTransPrices() {
        return $this->hasMany(GoodsTransPrice::className(), ['country_id' => 'id']);
    }
}
