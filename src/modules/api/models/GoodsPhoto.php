<?php

namespace app\modules\api\models;
use yii;
class GoodsPhoto extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{goods_photo}}';
    }

    public static function getDb()
    {
        return Yii::$app->get('db_shop');
    }

    public function scenarios()
    {
        return [
            'create' => ['goods_id', 'url','place','sku_id','type'],
            'update' => ['id', 'goods_id', 'url','place','sku_id','type'],
            'default' => ['id', 'goods_id', 'url','place','sku_id','type'],
        ];
    }
}
