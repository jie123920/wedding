<?php

namespace app\modules\shop\models;
use yii;
class GoodsCategoryIndex extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{goods_category_index}}';
    }

    public function scenarios() {
        return [
            'default' => ['goods_id', 'category_id', 'last_modify', 'shop_id','sort'],
        ];
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
