<?php
namespace app\modules\api\controllers;
use app\modules\api\services\Goods;
use app\helpers\myhelper;
use YII;
 
use app\modules\shop\models\Goods as G;
use app\modules\shop\models\GoodsCategory;
use yii\helpers\ArrayHelper;
class GoodsController extends CommonController
{
    public function init() {
        parent::init();
        parent::behaviors();
    }

    public function actionGet()
    {
        $goods_id = intval(YII::$app->request->get('goods_id',0));
        $kid = intval(YII::$app->request->get('kid',0));
        $lang = \yii::$app->request->get("lang",'en-us');
        $goods = Goods::get($goods_id,$kid,1,$lang);

        if($goods){
            //unset($goods['bread']);
            unset($goods['spec_value_ids']);
            unset($goods['gtm']);
            unset($goods['default_sku_info']);
            unset($goods['skuArr']);

            //good
            $goods['good']['cover'] = myhelper::resize($goods['good']['cover'],320,320);
            foreach ($goods['good']['goods_photo'] as &$p){
                $p = myhelper::resize($p,560,560);
            }

            //sku
            $sku = $sku_temp = [];
            foreach ($goods['productObj'] as $productObj){
                $sku_temp = [];
                $sku_temp = array_merge($sku_temp,$productObj['sorted_spec_value']);
                $sku_temp['spec_value_ids'] = $productObj['sorted_spec_value'];
                $sku_temp['price'] = $productObj['price'];
                $sku_temp['price_original'] = $productObj['price_original'];
                if(isset($productObj['weight']))
                $sku_temp['weight'] = $productObj['weight'];
                $sku_temp['count'] = $productObj['count'];
                $sku_temp['goods_sku_id'] = $productObj['id'];
                foreach ($productObj['photos'] as &$p){
                    $p = myhelper::resize($p,560,560);
                }
                $sku_temp['photos'] = $productObj['photos'];
                $sku[] = $sku_temp;
            }
            $goods['sku'] = $sku;
            unset($goods['productObj']);


            $specValArr = $s = [];
            foreach ($goods['specArr'] as $specArr){
                $specValArr[$specArr['spec_id']]['spec_name'] = $specArr['spec_name'];
                $specValArr[$specArr['spec_id']]['spec_id'] = $specArr['spec_id'];
                $specValArr[$specArr['spec_id']]['type'] = $specArr['type'];
                $specVal = $goods['specValArr'][$specArr['spec_id']];
                foreach ($specVal as $k=>&$_specVal){
                    //CUSTOM Size提到第一个
                    if(in_array($_specVal['spec_value_id'],explode(",",CUSTOM_ID))){
                        unset($specVal[$k]);
                        array_unshift($specVal,$_specVal);
                    }
                    $_specVal['spec_image'] = $_specVal['spec_image'] ? myhelper::resize($_specVal['spec_image'],70,70):'';
                }
                $specValArr[$specArr['spec_id']]['spec_values'] = array_values($specVal);
            }

            $goods['all_spec'] = array_values($specValArr);
            unset($goods['specValArr']);
            unset($goods['specArr']);

            if ($goods['bread']) {
                $top_category = current($goods['bread']);
                if($top_category){
                    $goods['good']['spec_json'] = isset(\yii::$app->params['size'][$top_category['id']])? \yii::$app->params['size'][$top_category['id']] : $goods['good']['spec_json'];
                }
                $goods['good']['spec_json'] = isset(\yii::$app->params['size'][$goods['good']['category_id']])? \yii::$app->params['size'][$goods['good']['category_id']] : $goods['good']['spec_json'];
            }


            //goods_photo
            $goods['goods_photo'] = $goods['good']['goods_photo'];
            unset($goods['good']['goods_photo']);


            if($goods['related_goods']){
                foreach ($goods['related_goods'] as &$related_goods){
                    $urltitle = preg_replace('/[^a-z0-9\s]/','',strtolower($related_goods['name']));
                    $urltitle = preg_replace('/\s+/','-',$urltitle);
                    $related_goods['urltitle'] = $urltitle;
                }
            }

            if($goods['recommend']){
                foreach ($goods['recommend'] as &$recommend){
                    $urltitle = preg_replace('/[^a-z0-9\s]/','',strtolower($recommend['name']));
                    $urltitle = preg_replace('/\s+/','-',$urltitle);
                    $recommend['urltitle'] = $urltitle;
                }
            }
        }


        return $this->result(0,$goods,'获取商品成功');
    }

    public function actionCategory(){
        $lang = \yii::$app->request->get('lang','en-us');
        $all_category = Goods::Categories(SHOP_ID,$lang);
        return $this->result(0,$all_category,'获取分类成功');
    }

    public function actionList(){
        $type = YII::$app->request->get('type','list_by_category');
        $lang = YII::$app->request->get('lang','en-us');
        $order = YII::$app->request->get('order','');
        $page = YII::$app->request->get('page',1);
        $per_page = YII::$app->request->get('per_page',16);
        $color = YII::$app->request->get('selectcolor','');
        $selectshape = YII::$app->request->get('selectshape','');
        $length = YII::$app->request->get('length','');
        $featrue = YII::$app->request->get('featrue','');
        $fabric = YII::$app->request->get('fabric','');
        $neckline = YII::$app->request->get('neckline','');
        $price_range = YII::$app->request->get('price_range', '');
        $price_range = $this->_check_price_range($price_range);
        $category_id = intval(YII::$app->request->get('category_id',0));
        $keywords = YII::$app->request->get('keywords','');
        $color = $color ? explode('-',$color) : [];
        $selectshape = $selectshape ? explode('-',$selectshape) : [];
        $length = $length ? explode('-',$length) : [];
        $featrue = $featrue ? explode('-',$featrue) : [];
        $fabric = $fabric ? explode('-',$fabric) : [];
        $neckline = $neckline ? explode('-',$neckline) : [];
        $spec = array_merge($color,$selectshape,$length,$featrue,$fabric,$neckline);
        $spec = implode('-',$spec);

        //Sale分类映射
        $sale = 0;
        if(isset(\yii::$app->params['sale'][$category_id])){
            $sale = 1;
        }
        //FROM GG ADS <<<
        $ads_keywords = YII::$app->request->get('ads_keyword', '');
        $utm_source   = YII::$app->request->get('utm_source', '');//GG ; FB
        $utm_campaign = YII::$app->request->get('utm_campaign', '');//'white_t-shirt'
        $ads_keywords = $utm_campaign ? $utm_campaign : $ads_keywords;
        $utm_content  = YII::$app->request->get('utm_content', '');//1,2,3
        //>>>


        if ($ads_keywords) $type = 'list_by_keyword';
        if ($utm_source) $type = 'list_by_gg';
        $params['ads_keywords'] = $ads_keywords;
        $params['utm_content']  = $utm_content ? implode(",", array_reverse(explode(",", $utm_content))) : "";

        $params['utm_source']   = $utm_source;
        $params['shop_id'] = SHOP_ID;
        $params['lang'] = $lang;
        $params['uid'] = isset($this->user_info['id'])?$this->user_info['id']:0;
        $params['category_id'] = $category_id;
        $params['type'] = $type;
        $params['keywords'] = $keywords;
        $params['spec'] =  $spec;
        $params['order'] = $order;
        $params['page'] = $page;
        $params['per_page'] = $per_page;
        $params['sale'] = $sale;
        $params['price_range']  = $price_range;
        $list = Goods::GoodsList($params);
        if(isset($list['data'])){
            foreach ($list['data'] as &$v){
                if(isset($v['cover']['url'])){
                    $v['cover'] = myhelper::resize($v['cover']['url'],360,540);
                }
            }
        }

        return $this->result(0,$list,'获取成功');
    }
    private function _check_price_range($price_range)
    {
        $price_range_    = [];
        $new_price_range = '';
        if ($price_range) {
            $price_range = explode('-', $price_range);
            foreach ($price_range as $i => $_price_range) {
                $_price_range = explode('_', $_price_range);
                if ($_price_range && count($_price_range) == 2 && isset($_price_range[0]) && isset($_price_range[1])) {
                    if (intval($_price_range[1]) > intval($_price_range[0])) {
                        $price_range_[$i] = intval($_price_range[0]) . '_' . intval($_price_range[1]);
                    }
                }
            }
            $new_price_range = implode('-', $price_range_);
        }
        return $new_price_range;
    }
    public function actionFavorite(){
        if(!$this->is_login){
            return $this->result(10001,[],'please log in first!');
        }
        $goods_id = Yii::$app->request->get('goods_id',0);
        $data = Goods::Favorite($goods_id,$this->user_info['id']);
         if (isset($data['code']) && $data['code'] == 0) {
            //谷歌统计<<<
            $good          = G::findOne($goods_id);
            $category_info = $category_ids = '';
            if ($good->categories) {
                $category_ids  = ArrayHelper::getColumn($good->categories, 'category_id');
                $category      = $good->categories[0];
                $category_info = GoodsCategory::findOne($category->category_id);
            }
            $langArr = ArrayHelper::map($good->language, 'table_field', 'content', 'language');
            $name    = isset($langArr[LANG_SET]['name']) ? $langArr[LANG_SET]['name'] : (isset($langArr['en-us']['name']) ? $langArr['en-us']['name'] : '');
            $name    = $name ? $name : (isset($langArr['en-us']['name']) ? $langArr['en-us']['name'] : '');
            $gtm     = [
                'name'     => $name,
                'id'       => $goods_id,
                'category' => $category_info ? $category_info->name : '',
            ];
            //谷歌统计>>>
 
        } 
        if(isset($data) && $data['code'] == 0){
            if(isset($gtm))
            return $this->result(0,$gtm,$data['message']);
            else
            return $this->result(0,$gtm,$data['message']);
        }else{
            return $this->result(1,[],$data['message']);
        }
    }

    public function actionMyFavorite(){
        if(!$this->is_login){
            return $this->result(10001,[],'please log in first!');
        }
        $lang = YII::$app->request->get('lang','en-us');
        $order = YII::$app->request->get('order','');
        $page = YII::$app->request->get('page',1);
        $list = Goods::MyFavorite($order,$lang,$page,$this->user_info['id']);
        return $this->result(0,$list,'get favorite goods successful!');
    }

    public function actionListFilter(){
        $lang = YII::$app->request->get('lang','en-us');
        $category_id = YII::$app->request->get('category_id',0);
        $selected_spec = YII::$app->request->get('selected_spec', '');
        $selected_price = YII::$app->request->get('selected_price', '');
        $list = Goods::ListFilter($category_id,$selected_spec,$selected_price,$lang);
        return $this->result(0,$list,'get successful!');
    }

    public function actionComment(){
        if($this->is_login!=1){
            return $this->result(-1,[],'please login');
        }

        $goods_id = Yii::$app->request->post('goods_id',0);
        if($goods_id==0){
            return $this->result(1,[],'no goods');
        }
        if(\Yii::$app->cache->get(CACHE_PREFIX.'_comment_'.$this->user_info['id'])){
            return $this->result(1,[],'The operating interval must not be less than 60 seconds!');
        }
        $star = Yii::$app->request->post('star',5);
        $content = Yii::$app->request->post('content','');
        $picture = Yii::$app->request->post('picture','');
        $picture_url = [];
        if($picture){
            $picture = json_decode($picture,true);
            if($picture)
            foreach ($picture as $_picture){
                $url  = myhelper::base64_image_upload($_picture);
                if($url)
                $picture_url[] = $url;
            }
        }

        $data = Goods::Comment($goods_id,SHOP_ID,$content,$star,json_encode($picture_url),$this->user_info['id']);
        if($data && isset($data['code'])){
            if($data['code'] == 0){
                //更新商品缓存
                Goods::DelGoodDetailCache($goods_id);
                //更新评论列表缓存
                Goods::DelCommentCache($goods_id);
                //每次评论的间隔是60秒
                \Yii::$app->cache->set(CACHE_PREFIX.'_comment_'.$this->user_info['id'],1,60);
                return $this->result(0,[],'comment successful');
            }else{
                return $this->result($data['code'],[],$data['message']);
            }
        }
        return $this->result(1,[],'unknown error!');
    }

    public function actionReplyList(){
        $good_id = \yii::$app->request->get('goods_id',0);
        $data =  Goods::CommentList($good_id);
        if(isset($data['data']['list']['comment'])){
            foreach ($data['data']['list']['comment'] as &$_comment){
                $pic = json_decode($_comment['picture'],true);
                if($pic){
                    $_comment['picture'] = $pic;
                }else{
                    $_comment['picture'] = '';
                }
            }
        }
        return $this->result(0,$data['data'],'ok!');
    }

    public function actionSkuPhotos(){
        $kid = \yii::$app->request->get('kid',0);
        $data =  Goods::SkuPhotos($kid);

        return $this->result(0,$data['data'],'ok!');
    }
     public function actionGetlocationinfobyip()
    {
        $ips  = \yii::$app->request->get('ip', '');
        $client = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote = @$_SERVER['REMOTE_ADDR'];
        $result = array('country' => '', 'city' => '');
        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = $remote;
        }
        // var_dump($ip);
        if($ips) $ip= $ips;
        $ip_data = @json_decode
        (file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
         
        var_dump( $ip_data);exit;
        
    }

}
