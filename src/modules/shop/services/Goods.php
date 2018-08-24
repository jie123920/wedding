<?php
namespace app\modules\shop\services;
use app\modules\shop\models\GoodsCategory;
use app\modules\shop\models\Goods as G;

use app\modules\shop\models\GoodsSpec;
use app\modules\shop\models\BlockZhuanti;
use app\modules\shop\models\GoodsFavorite;
use app\modules\shop\models\GoodsBrand;
class Goods
{
    //商品分类
    public static function Category($lang,$shop_id){
        return (new GoodsCategory())->get_categories_tree(0,$lang,$shop_id);
    }

    //商品列表
    public static function GoodList($type,$uid,$category_id,$spec,$designer,$keywords,$ads_keywords,$r_id,$orderBy,$lang,$page,$shop_id,$rate){
        $cate_name = 'All';
        $category_info = [];
        switch ($type){
            case 'list_by_category':
                if(!$category_id) $orderBy = 'sort DESC,'.$orderBy;//全局排序
                $category_info = $GoodsCategory = GoodsCategory::One($category_id,$lang);
                $list = G::list_by_category($uid,$category_id,$spec,$designer,$orderBy,$lang,$page,$shop_id,$rate);
                break;
            case 'list_by_search':
                $cate_name = $keywords;
                $list = G::list_by_search($uid,$keywords,$spec,$designer,$orderBy,$lang,$page,$shop_id,$rate);
                break;
            case 'list_by_keyword'://按广告关键词
                $cate_name = $ads_keywords;
                $list = G::list_by_keywords($uid,$ads_keywords,$spec,$designer,$orderBy,$lang,$page,$shop_id,$rate);
                break;
            case 'list_by_recommend'://按推荐
                $recommend = GoodsRecommend::findOne($r_id);
                if($recommend->language){
                    $cate_name = $recommend->language[0]->content;
                }
                $list = G::list_by_recommend($uid,$r_id,$spec,$designer,$orderBy,$lang,$page,$shop_id,$rate);
                break;
        }

        if($list){
            foreach ($list['data'] as &$_list){
                $_list['BN'] = json_decode($_list['BN']);
            }
            if(isset($category_info)){
                $list['cate_name'] = isset($category_info['name'])?$category_info['name']:'';
            }
        }
        $list['cate_name'] = isset($list['cate_name'])?$list['cate_name']:$cate_name;//当前查询的关键字 或者当前分类名
        $list['category_info'] = $category_info;

        return $list;
    }

    //筛选条件
    public static function ListFilter($lang){

        $size_color = GoodsSpec::get_size_color($lang);
        $brand = GoodsBrand::get_all_api();
        $list['size'] = array_values($size_color['size']);
        $list['color'] = array_values($size_color['color']);
        $list['brand'] = $brand;

        return $list;
    }

    //专题
    public static function Special($uid,$cid,$id,$shop_id,$lang,$page){

        $cacheKey = CACHE_PREFIX."_".__FUNCTION__.'_'.$page.'_'.$cid.'_'.$id.'_'.$shop_id.'_'.$lang;
        if ($return = \Yii::$app->redis->get($cacheKey)) {
            $return = unserialize($return);
            $return['data']['data'] = G::_init_goods_hot_data($return['data']['data'],$uid);
            return $return;
        }


        $block = BlockZhuanti::findOne($id);
        if(!$block){
            return false;
        }
        if($block->status == 0){
            return false;
        }

        $list = [];
        $goods = $block->good_ids?json_decode($block->good_ids):'';

        if($goods){
            if($cid){
                $cate_child = (new GoodsCategory)->getChild($cid);
                $cate_child[] = $cid;
                $list = G::find()
                    ->select('goods.*')
                    ->join('RIGHT JOIN','goods_category_index as i','i.goods_id=goods.id')
                    ->where(['goods.id'=>$goods,'goods.status'=>1,'i.category_id'=>$cate_child])
                    ->orderBy([new \yii\db\Expression('FIELD (goods.id, '.implode(",",$goods).')')])
                    ->limit(G::PER_PAGE)
                    ->offset(($page-1)*G::PER_PAGE)
                    ->asArray()
                    ->all();
                $count  = G::find()
                    ->select('goods.*')
                    ->join('RIGHT JOIN','goods_category_index as i','i.goods_id=goods.id')
                    ->where(['id'=>$goods,'status'=>1,'i.category_id'=>$cate_child])
                    ->count();
            }else{
                $list = G::find()->where(['id'=>$goods,'status'=>1])->orderBy([new \yii\db\Expression('FIELD (id, '.implode(",",$goods).')')])->limit(G::PER_PAGE)->offset(($page-1)*G::PER_PAGE)->asArray()->all();
                $count = G::find()->where(['id'=>$goods,'status'=>1])->count();
            }
        }
        $data = [];
        if($list){
            $list = G::_get_spec($list,$lang,$uid);
            $data = ['total_num'=>$count,'page'=>$page,'per_page'=>G::PER_PAGE,'data'=>$list];
        }

        $return = [
            'block'=>$block,
            'data'=>$data,
        ];
        \Yii::$app->redis->set($cacheKey, serialize($return));
        \Yii::$app->redis->expire($cacheKey,CACHE_EXPIRE);

        return $return;
    }

    //喜欢商品的动作
    public static function Favorite($data){

        $if_favorite = GoodsFavorite::find()
            ->where(['goods_id'=>$data['goods_id'],'uid'=>$data['uid']])
            ->one();
        if($if_favorite){
            (new GoodsFavorite())->deleteAll(['goods_id'=>$data['goods_id'],'uid'=>$data['uid']]);
            return true;
        }

        $GoodsFavorite = new GoodsFavorite;
        $GoodsFavorite->setScenario('create');
        $GoodsFavorite->isNewRecord = true;
        $GoodsFavorite->setAttributes($data);
        if(!$GoodsFavorite->save($data)){
            foreach ($GoodsFavorite->getErrors() as $key=>$error){
                $exception = $error[0];break;
            }
            return false;
        }
        return true;
    }

    //获取sku信息
    public static function products($sku_ids = [],$shop_id=SHOP_ID,$lang=LANG_SET,$rate=1){
        $data = [];
        if ($sku_ids){
            foreach ($sku_ids as $goods_sku_id){
                $goods_info = G::One(null,$goods_sku_id,$shop_id,$lang,$rate);
                if(!$goods_info) continue;
                $spec = [];
                foreach ($goods_info['productObj'] as $spec_value_id => $productObj){
                    if($productObj->id == $goods_sku_id){
                        $photos = $productObj->photos;
                        foreach ($goods_info['specValArr'] as $spec_id=>$specValArr){
                            foreach ($specValArr as $_specValArr){
                                if(in_array($_specValArr->spec_value_id,explode(';',$spec_value_id))){
                                    $value = $_specValArr->toArray();
                                    $value['spec_name'] = $goods_info['specArr'][$spec_id]['spec_name'];
                                    $value['type'] = $goods_info['specArr'][$spec_id]['spec_type'];
                                    $spec[$spec_id] = $value;
                                }
                            }
                        }
                    }
                }

                $spec_sorted = $goods_info['specArr'];
                foreach ($spec_sorted as $spec_id =>$_spec_sort){
                    $spec_sorted[$spec_id] = $spec[$spec_id];
                }
                $_product = $goods_info['default_sku_info']->toArray();
                $_product['spec'] = $spec_sorted;
                $_product['cover'] = isset($photos[0])?$photos[0]:$goods_info['good']['cover'];
                $_product['name'] = $goods_info['good']['name'];
                $_product['good_status'] = $goods_info['good']['status'];
                
                $data[] = $_product;
            }
            return $data;
        }
    }
}