<?php
namespace app\modules\shop\models;
use Yii;

class BlockData extends \yii\db\ActiveRecord
{

    public static function getDb() {
        return Yii::$app->get('db_shop');
    }
    public static function tableName()
    {
        return 'block_data';
    }

    public static function get_block_data($block_id,$num){

        $cacheKey = CACHE_PREFIX."_block_".$block_id."_".$num;
        if ($data = \Yii::$app->redis->hget(CACHE_PREFIX."_block_".$block_id,$cacheKey)) {
            return unserialize($data);
        }
        $data = self::find()
            ->select("block_data.*,block.name as block_name")
            ->join('right join','block','block.id=block_data.block_id')
            ->where(['block_data.block_id'=>$block_id,'block_data.status'=>1])
            ->limit($num)
            ->orderBy('block_data.sort DESC')
            ->asArray()
            ->all();

        \Yii::$app->redis->hset(CACHE_PREFIX."_block_".$block_id,$cacheKey, serialize($data));
        \Yii::$app->redis->expire(CACHE_PREFIX."_block_".$block_id,CACHE_EXPIRE);

        return $data;
    }
}
