<?php

namespace app\modules\api\models;

use Yii;

/**
 * This is the model class for table "shop_exchange_rate".
 *
 * @property integer $id
 * @property string $project_id
 * @property integer $currency_id_base
 * @property string $amount_base
 * @property integer $currency_id
 * @property string $amount
 * @property string $exchange_rate
 * @property integer $status
 * @property integer $updatetime
 * @property integer $createtime
 */
class ShopExchangeRate extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_exchange_rate';
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
            [['currency_id_base', 'currency_id', 'status', 'updatetime', 'createtime'], 'integer'],
            [['amount_base', 'amount', 'exchange_rate'], 'number'],
            [['project_id'], 'string', 'max' => 255]
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
            'currency_id_base' => 'Currency Id Base',
            'amount_base' => 'Amount Base',
            'currency_id' => 'Currency ID',
            'amount' => 'Amount',
            'exchange_rate' => 'Exchange Rate',
            'status' => 'Status',
            'updatetime' => 'Updatetime',
            'createtime' => 'Createtime',
        ];
    }
}
