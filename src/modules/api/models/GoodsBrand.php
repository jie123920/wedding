<?php

namespace app\modules\api\models;
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
        $cacheKey = 'shop_get_all_brand';
        if ($return = \Yii::$app->cache->get($cacheKey)) {
             return $return;
        }
        $brand = GoodsBrand::find()->all();
        if ($brand)
            $brand = ArrayHelper::map($brand,'brand_id','brand_name');
        \Yii::$app->cache->set($cacheKey, $brand, 600);

        return $brand;
    }

    public static function get_all_api(){
        $cacheKey = 'shop_get_all_api_brand';
        if ($return = \Yii::$app->cache->get($cacheKey)) {
            //return $return;
        }
        $brand = GoodsBrand::find()->asArray()->all();
        foreach ($brand as &$v){
            $v['brand_id'] = intval($v['brand_id']);
        }


        \Yii::$app->cache->set($cacheKey, $brand, 600);

        return $brand;
    }
}
