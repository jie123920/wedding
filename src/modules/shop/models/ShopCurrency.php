<?php

namespace app\modules\shop\models;

use Yii;

/**
 * This is the model class for table "shop_currency".
 *
 * @property integer $id
 * @property string $project_id
 * @property string $name
 * @property string $name_zh
 * @property string $symbol
 * @property integer $status
 * @property integer $updatetime
 * @property integer $createtime
 */
class ShopCurrency extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_currency';
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
            [['project_id', 'name', 'name_zh', 'symbol'], 'string', 'max' => 255],
            [['project_id', 'name', 'symbol'], 'unique', 'targetAttribute' => ['project_id', 'name', 'symbol'], 'message' => 'The combination of Project ID, Name and Symbol has already been taken.']
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
            'name' => 'Name',
            'name_zh' => 'Name Zh',
            'symbol' => 'Symbol',
            'status' => 'Status',
            'updatetime' => 'Updatetime',
            'createtime' => 'Createtime',
        ];
    }
}
