<?php

namespace modules\shop\models;

use Yii;

/**
 * This is the model class for table "goods_marketing_postal".
 *
 * @property integer $id
 * @property string $type
 * @property string $title
 * @property double $price
 * @property integer $created_time
 * @property integer $update_time
 */
class GoodsMarketingPostal extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'goods_marketing_postal';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db2');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['price'], 'number'],
            [['created_time', 'update_time'], 'integer'],
            [['type'], 'string', 'max' => 256],
            [['title'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'type' => Yii::t('app', 'Type'),
            'title' => Yii::t('app', 'Title'),
            'price' => Yii::t('app', 'Price'),
            'created_time' => Yii::t('app', 'Created Time'),
            'update_time' => Yii::t('app', 'Update Time'),
        ];
    }
}