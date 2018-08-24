<?php
namespace app\modules\shop\controllers;

use app\modules\shop\models\Goods;
use YII;
class UpdateCacheController extends CommonController
{
    public function init() {
        parent::init();
    }

    /**
     * 删除商品详情页缓存
     * @return array
     */
    public function actionDelGoodDetailCache()
    {
        $good_id = YII::$app->request->get('id','');

        try {
            //删除商品详情页缓存
            \Yii::$app->redis->del(CACHE_PREFIX.'_good_one_'.$good_id);
            //删除商品缓存的重要数据字段
            \Yii::$app->cache->delete(Goods::getKey($good_id));
        } catch (\Exception $e ) {
            YII::error( $e->getFile() . ':' . $e->getLine() . ':' . $e->getMessage() );
        }


        return $this->result(0,[],'OK');
    }


    /**
     * 删除商品列表页缓存
     * @return array
     */
    public function actionDelGoodsListCache()
    {
        $category_id = YII::$app->request->get('category_id',0);

        $category_id = explode('-',$category_id);
        
        try {
            //统一删除分类hash 缓存
            foreach($category_id as $id){
                \Yii::$app->cache->delete(CACHE_PREFIX."_goods_list_by_category_id:$id");
            }
            //统一删除 活动 推荐位 广告关键词 hash 缓存
            \Yii::$app->cache->delete(CACHE_PREFIX."_goods_list");
        } catch (\Exception $e ) {
            YII::error( $e->getFile() . ':' . $e->getLine() . ':' . $e->getMessage() );
        }

        return $this->result(0,[],'OK');
    }



    /**
     * 删除首页推荐位缓存
     * @return array
     */
    public function actionDelBlockCache()
    {
        $block_id = YII::$app->request->get('id','');

        try {
            \Yii::$app->redis->del(CACHE_PREFIX."_block_".$block_id);
        } catch (\Exception $e ) {
            YII::error( $e->getFile() . ':' . $e->getLine() . ':' . $e->getMessage() );
        }

        return $this->result(0,[],'OK');
    }



    /**
     * 删除评论缓存
     * @return array
     */
    public function actionDelCommentCache()
    {
        $good_id = YII::$app->request->get('good_id','');
        $shop_id = YII::$app->request->get('shop_id',SHOP_ID);
        try {
            \Yii::$app->redis->del(CACHE_PREFIX.'_good_reply_list_'.$good_id.'_'.$shop_id);
        } catch (\Exception $e ) {
            YII::error( $e->getFile() . ':' . $e->getLine() . ':' . $e->getMessage() );
        }

        return $this->result(0,[],'OK');
    }

}
