<?php

namespace app\modules\shop\models;
use yii\helpers\ArrayHelper;
use Yii;
class GoodsBrand extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{goods_brand}}';
    }
    public static function getDb() {
        return Yii::$app->get('db_shop');
    }

    public static function get_all(){
        $cacheKey = CACHE_PREFIX."_".__FILE__.__FUNCTION__;
        if ($return = \Yii::$app->redis->get($cacheKey)) {
             return unserialize($return);
        }
        $brand = GoodsBrand::find()->all();
        if ($brand)
            $brand = ArrayHelper::map($brand,'brand_id','brand_name');

        \Yii::$app->redis->set($cacheKey, serialize($brand));
        \Yii::$app->redis->expire($cacheKey,CACHE_EXPIRE);

        return $brand;
    }

    public static function get_all_api(){
        $cacheKey = CACHE_PREFIX."_".__FILE__.__FUNCTION__;
        if ($return = \Yii::$app->redis->get($cacheKey)) {
            return unserialize($return);
        }

        $brand = GoodsBrand::find()->asArray()->all();

        \Yii::$app->redis->set($cacheKey, serialize($brand));
        \Yii::$app->redis->expire($cacheKey,CACHE_EXPIRE);

        return $brand;
    }
}
