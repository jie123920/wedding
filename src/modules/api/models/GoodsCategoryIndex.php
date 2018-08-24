<?php

namespace app\modules\api\models;
use yii;
class GoodsCategoryIndex extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{goods_category_index}}';
    }

    public static function getDb() {
        return Yii::$app->get('db_shop');
    }

    public function getLanguage($lang = LANG_SET)
    {
        return $this->hasMany(GoodsLanguage::className(), ['table_id' => 'id'])
            ->where(['table_field'=>'name','table_name'=>'goods_category','language'=>$lang]);
    }
}
