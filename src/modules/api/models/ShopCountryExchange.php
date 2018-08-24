<?php

namespace app\modules\api\models;

use Yii;

/**
 * This is the model class for table "shop_country_exchange".
 *
 * @property integer $id
 * @property integer $project_id
 * @property integer $country_id
 * @property integer $exchange_rate_id
 * @property integer $status
 * @property integer $updatetime
 * @property integer $createtime
 */
class ShopCountryExchange extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_country_exchange';
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
            [['project_id', 'country_id', 'exchange_rate_id', 'status', 'updatetime', 'createtime'], 'integer'],
            [['country_id', 'exchange_rate_id'], 'unique', 'targetAttribute' => ['country_id', 'exchange_rate_id'], 'message' => 'The combination of Country ID and Exchange Rate ID has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'project_id' => 'Project ID',
            'country_id' => 'Country ID',
            'exchange_rate_id' => 'Exchange Rate ID',
            'status' => 'Status',
            'updatetime' => 'Updatetime',
            'createtime' => 'Createtime',
        ];
    }
}
