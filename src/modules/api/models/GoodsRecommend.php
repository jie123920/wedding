<?php

namespace app\modules\api\models;
use yii\helpers\ArrayHelper;
use Yii;
class GoodsRecommend extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{goods_recommend}}';
    }
    public static function getDb() {
        return Yii::$app->get('db_shop');
    }

    public function getLanguage($lang = LANG_SET)
    {
        return $this->hasMany(GoodsLanguage::className(), ['table_id' => 'id'])
            ->where(['table_field'=>'name','table_name'=>'goods_recommend','language'=>$lang])
            ;
    }
}
