<?php

namespace app\modules\shop\models;
use Yii;
class GoodsKeywordsIndex extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{goods_keywords_index}}';
    }
    public static function getDb() {
        return Yii::$app->get('db_shop');
    }

}
