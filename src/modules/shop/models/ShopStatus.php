<?php

namespace app\modules\shop\models;

use Yii;

/**
 * This is the model class for table "shop_status".
 *
 * @property integer $id
 * @property integer $uid
 * @property string $content_en
 * @property string $content_cn
 * @property integer $status
 * @property integer $createtime
 */
class ShopStatus extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_status';
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
            [['uid', 'status', 'createtime'], 'integer'],
            [['content_en', 'content_cn'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => 'Uid',
            'content_en' => 'Content En',
            'content_cn' => 'Content Cn',
            'status' => 'Status',
            'createtime' => 'Createtime',
        ];
    }
}
