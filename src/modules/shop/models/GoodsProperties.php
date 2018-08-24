<?php

namespace app\modules\shop\models;

use Yii;

/**
 * This is the model class for table "goods_properties".
 *
 * @property string $id
 * @property string $goods_id
 * @property string $name
 * @property string $value
 * @property integer $sort
 * @property integer $status
 * @property string $createdtime
 * @property string $updatedtime
 */
class GoodsProperties extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{goods_properties}}';
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
            [['goods_id', 'sort', 'status', 'createdtime', 'updatedtime'], 'integer'],
            [['name'], 'string'],
            [['value'], 'string', 'max' => 200]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'goods_id' => 'Goods ID',
            'name' => 'Name',
            'value' => 'Value',
            'sort' => 'Sort',
            'status' => 'Status',
            'createdtime' => 'Createdtime',
            'updatedtime' => 'Updatedtime',
        ];
    }
}
