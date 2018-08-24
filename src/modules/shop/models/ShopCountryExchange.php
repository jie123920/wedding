<?php

namespace app\modules\shop\models;

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
    
    public static function listInfo(){
        $result = self::getDb()->cache(function () {
            $list = ShopCountryExchange::find()->alias('A')
            ->select(['A.id', 'A.country_id', 'B.currency_id', 'B.amount m', 'C.name', 'C.symbol'])
            ->leftJoin('shop_exchange_rate B', 'A.exchange_rate_id=B.id')
            ->leftJoin('shop_currency C', 'B.currency_id=C.id')
            ->where(['A.status'=>1])
            ->asArray()->all();
            
            return $list;
        },3600);
            
        return $result;
    }
}
