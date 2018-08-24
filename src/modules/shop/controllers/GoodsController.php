<?php
namespace app\modules\shop\controllers;
use app\modules\shop\models\BlockData;
use app\modules\shop\services\Goods;
use app\modules\shop\models\Goods as G;
use YII;
class GoodsController extends CommonController
{
    public function init() {
        parent::init();
    }

    public function actionGet() {
        $goods_id = (int) Yii::$app->request->get('goods_id');
        $sku_id = (int) Yii::$app->request->get('kid');
        $lang = Yii::$app->request->get('lang','en-us');
        $shop_id = (int) Yii::$app->request->get('shop_id',SHOP_ID);
        $rate = intval(YII::$app->request->get('rate',THINK_RATE_M));
        $data = G::One($goods_id,$sku_id,$shop_id,$lang,$rate);
        return $this->result(0,$data,'获取商品成功');
    }

    public function actionCategory(){
        $shop_id = YII::$app->request->get('shop_id','1');
        $lang = YII::$app->request->get('lang','en-us');
        $all_category = Goods::Category($lang,$shop_id);
        return $this->result(0,$all_category,'获取分类成功');
    }

    public function actionList(){
        $shop_id = intval(YII::$app->request->get('shop_id',SHOP_ID));
        $uid = \Yii::$app->request->get('uid',0);
        $type = YII::$app->request->get('type','list_by_category');
        $lang = YII::$app->request->get('lang','en-us');
        $order = YII::$app->request->get('order','');
        $page = YII::$app->request->get('page',1);
        $spec = YII::$app->request->get('spec','');
        $designer = YII::$app->request->get('selectdesigner',[]);
        $ads_keywords = YII::$app->request->get('ads_keyword','');
        $keywords = YII::$app->request->get('keywords','');
        $r_id  = intval(YII::$app->request->get('r_id',0));
        $category_id = intval(YII::$app->request->get('category_id',0));
        $rate = intval(YII::$app->request->get('rate',1));
        $selectspec = [];
        if($spec) $selectspec = explode('-',$spec);
        if($designer) $designer = explode('-',$designer);
        if($ads_keywords) $type = 'list_by_keyword';
        if($r_id) $type='list_by_recommend';

        switch ($order){
            case 'price':
                $orderBy = 'price DESC';
                break;
            case '-price':
                $orderBy = 'price ASC';
                break;
            case 'up_time':
                $orderBy = 'up_time DESC';
                break;
            case 'sell':
                $orderBy = 'sell DESC';
                break;
            default :
                $orderBy = 'up_time DESC';
                $orderBy = 'i.sort desc,'.$orderBy;
                if($ads_keywords){//广告关键词所属商品排序优先
                    $orderBy = 'gki.id ASC';
                }
                break;
        }

        $list = Goods::GoodList($type,$uid,$category_id,$selectspec,$designer,$keywords,$ads_keywords,$r_id,$orderBy,$lang,$page,$shop_id,$rate);

        return $this->result(0,$list,'获取成功');
    }

    public function actionFavorite(){
        $uid = \Yii::$app->request->get('uid',0);
        $goods_id = Yii::$app->request->get('goods_id',0);
        if(!$uid){
            return  $this->result(1,[],'uid is empty!');
        }
        $goods_info = G::findOne($goods_id);
        if(!$goods_info){
            return $this->result(1,[],'no this goods');
        }
        $data = ['goods_id'=>$goods_id,'uid'=>$uid,'last_modify'=>time()];

        if(!Goods::Favorite($data)){
            return $this->result(1,[],'error');
        }else{
            return $this->result(0,[],1);
        }
    }

    public function actionMyFavorite(){
        $uid = \Yii::$app->request->get('uid',0);
        if(!$uid){
            return  $this->result(1,[],'uid is empty!');
        }
        $lang = YII::$app->request->get('lang','en-us');
        $order = YII::$app->request->get('order','');
        $page = YII::$app->request->get('page',1);
        switch ($order){
            case 'price':
                $orderBy = 'price ASC';
                break;
            case '-price':
                $orderBy = 'price DESC';
                break;
            case 'up_time':
                $orderBy = 'up_time DESC';
                break;
            case 'sell':
                $orderBy = 'sell DESC';
                break;
            default :
                $orderBy = 'up_time DESC';
                break;
        }
        $list = G::list_by_favorite($uid,$orderBy,$lang,$page);
        return $this->result(0,$list,'get favorite goods successful!');
    }

    public function actionListFilter(){
        $lang = YII::$app->request->get('lang','en-us');
        $list = Goods::ListFilter($lang);
        return $this->result(0,$list,'get successful!');
    }

    //专题页
    public function actionSpecial(){
        $uid = \Yii::$app->request->get('uid',0);
        $page  = \Yii::$app->request->get('page',1);
        $id  = \Yii::$app->request->get('id',0);
        $cid  = \Yii::$app->request->get('cid',0);
        $lang = \Yii::$app->request->get('lang','en-us');
        $shop_id = \Yii::$app->request->get('shop_id',SHOP_ID);

        $return = Goods::Special($uid,$cid,$id,$shop_id,$lang,$page);

        return $this->result(0,$return,'get successful!');
    }

    public function actionBlock(){
        $id = \yii::$app->request->get('id',0);
        $num = \yii::$app->request->get('num',1);
        $data = BlockData::get_block_data($id,$num);
        return  $this->result(0,$data,'get ok!');
    }
}
