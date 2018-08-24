<?php

/**
 * Created by IntelliJ IDEA.
 * User: shihuipeng
 * Date: 2017/6/9
 * Time: 上午11:40
 */

namespace app\modules\api\models;

use Yii;
use yii\db\ActiveRecord;

class BuyRealRec extends ActiveRecord
{
    /**
     * @inheritdoc
     */

    public static function getDb()
    {
        return Yii::$app->get('db_shop');
    }

    public static function tableName()
    {
        return 'buyreal_rec';
    }

    public static function search($param)
    {
        $findParams = ['buyreal.buy_more_url','goods.id', 'goods.cover', 'goods.price', 'goods.price_original', 'goods.price_min', 'goods.price_max', 'goods.discount_min', 'goods.discount_max', 'goods.up_time', 'goods.type', 'goods.name', 'goods.link'];
        $data = self::find()->select($findParams)
            ->alias('buyreal')->where($param)->leftJoin('goods', 'goods.bn = buyreal.goods_bn')->asArray()->all();
        return $data;
    }

    public static function getBuyReal($condition)
    {
        $findParams = ['buyreal.buy_more_url', 'goods.id', 'goods.cover', 'goods.price', 'goods.price_original', 'goods.price_min', 'goods.price_max', 'goods.discount_min', 'goods.discount_max', 'goods.up_time', 'goods.type', 'goods.name', 'goods.link'];
        return self::find()->select($findParams)->alias('buyreal')->where($condition)->leftJoin('goods', 'goods.bn = buyreal.goods_bn')->orderBy('buyreal.id desc')->limit(4)->asArray()->all();
    }

    public function getGoods()
    {
        return $this->hasOne(GoodsProperties::className(), ['goods_id' => 'id']);
    }

    public static function getRegionInfo($param)
    {
        $findParams = ['goods.id', 'goods.cover', 'goods.price', 'goods.price_original','goods.price_min', 'goods.price_max', 'goods.discount_min', 'goods.discount_max',  'goods.up_time', 'goods.type', 'goods.name'];
        $data = self::find()->select($findParams)
            ->alias('buyreal')->where($param)->leftJoin('goods', 'goods.id = buyreal.goods_id')->asArray()->all();
        return $data;
    }

}
