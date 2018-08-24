<?php

namespace app\modules\shop\models;

use Yii;

/**
 * This is the model class for table "shop_abnormal".
 *
 * @property integer $id
 * @property string $shop_orderid
 * @property string $project_id
 * @property string $source
 * @property string $uid
 * @property string $email
 * @property string $orderid
 * @property string $channel_orderid
 * @property string $channel
 * @property string $channel_method
 * @property string $amount
 * @property string $apply_amount
 * @property string $reality_amount
 * @property string $currency
 * @property string $currency_symbol
 * @property string $remark
 * @property string $reason
 * @property string $reply
 * @property string $istest
 * @property integer $status
 * @property integer $updatetime
 * @property integer $createtime
 */
class ShopAbnormal extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_abnormal';
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
            [['status', 'updatetime', 'createtime'], 'integer'],
            [['shop_orderid', 'project_id', 'source', 'amount', 'reason', 'reply'], 'string', 'max' => 255],
            [['uid', 'orderid', 'channel_orderid', 'channel', 'channel_method', 'apply_amount', 'reality_amount', 'istest'], 'string', 'max' => 64],
            [['email'], 'string', 'max' => 128],
            [['currency', 'currency_symbol'], 'string', 'max' => 8],
            [['remark'], 'string', 'max' => 256]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'shop_orderid' => 'Shop Orderid',
            'project_id' => 'Project ID',
            'source' => 'Source',
            'uid' => 'Uid',
            'email' => 'Email',
            'orderid' => 'Orderid',
            'channel_orderid' => 'Channel Orderid',
            'channel' => 'Channel',
            'channel_method' => 'Channel Method',
            'amount' => 'Amount',
            'apply_amount' => 'Apply Amount',
            'reality_amount' => 'Reality Amount',
            'currency' => 'Currency',
            'currency_symbol' => 'Currency Symbol',
            'remark' => 'Remark',
            'reason' => 'Reason',
            'reply' => 'Reply',
            'istest' => 'Istest',
            'status' => 'Status',
            'updatetime' => 'Updatetime',
            'createtime' => 'Createtime',
        ];
    }
}
