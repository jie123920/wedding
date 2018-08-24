<?php

namespace app\modules\api\models;

use Yii;

/**
 * This is the model class for table "shop_order_flow".
 *
 * @property integer $id
 * @property string $orderid
 * @property string $type
 * @property integer $status
 * @property integer $updatetime
 * @property integer $createtime
 */
class ShopOrderFlow extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_order_flow';
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
            [['orderid'], 'required'],
            [['status', 'updatetime', 'createtime'], 'integer'],
            [['orderid', 'type'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'orderid' => 'Orderid',
            'type' => 'Type',
            'status' => 'Status',
            'updatetime' => 'Updatetime',
            'createtime' => 'Createtime',
        ];
    }
}
