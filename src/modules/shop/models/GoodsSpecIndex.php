<?php

namespace app\modules\shop\models;
use yii;
class GoodsSpecIndex extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{goods_spec_index}}';
    }

    public static function getDb() {
        return Yii::$app->get('db_shop');
    }
    public function scenarios() {
        return [
            'create' => ['spec_value_id','goods_id','goods_sku_id','last_modify'],
            'update' => ['spec_value_id','goods_id','goods_sku_id','last_modify'],
            'default' => ['spec_value_id','goods_id','goods_sku_id','last_modify'],
        ];
    }
}
