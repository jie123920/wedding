<?php

namespace app\modules\shop\models;
use Yii;
class GoodsKeywords extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{goods_keywords}}';
    }
    public static function getDb() {
        return Yii::$app->get('db_shop');
    }

}
