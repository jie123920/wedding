<?php
namespace app\modules\wedding\services;
use app\helpers\myhelper;
use Yii;
class Goods extends Service{
    const PER_PAGE = 20;
    const PER_PAGE_SHOW = 200;
    //获取单个商品
    public static function  get($goods_id=0,$kid=0,$rate=1){
        $ips = YII::$app->request->get('ip', '');//'23.10.1.63';//
        $getip = '';//\Yii::$app->getRequest()->getUserIP();
        if($ips)  $getip = $ips;
        $cityname = self::getLocationInfoByIp($getip);  
        $cantseegoods = [];
         $res = myhelper::sendRequest(['area_name'=>$cityname,'shop_id'=>SHOP_ID],'GET',true,SHOP_API_URL.'goods/goods-shield');
        if($res && isset($res['code'])){
            if($res['code'] == 0){
                 $cantseegoods = $res['data'];
            }
        }
     
        if($cantseegoods){
            if(in_array($goods_id,$cantseegoods)){
                return false;
            }
        }

        $params = [
            'goods_id'=>$goods_id,
            'kid'=>$kid,
            'shop_id'=>SHOP_ID,
            'lang'=>LANG_SET,
            'rate'=>$rate
        ];
        $params = self::encode($params);
        $data = myhelper::sendRequest($params,'GET',true,SHOP_API_URL.'goods/get');
        if($data && isset($data['code'])){
            if($data['code'] == 0){
                return  $data['data'];
            }
        }
        return false;
    }

    //商品列表 搜索 广告落地  分类商品
    public static function GoodsList($params = []){
        $ips = YII::$app->request->get('ip', '');//'23.10.1.63';//
        $getip = '';//\Yii::$app->getRequest()->getUserIP();
        if($ips)  $getip = $ips;
        $cityname = self::getLocationInfoByIp($getip);  
        $cantseegoods = [];
         $res = myhelper::sendRequest(['area_name'=>$cityname,'shop_id'=>SHOP_ID],'GET',true,SHOP_API_URL.'goods/goods-shield');
        if($res && isset($res['code'])){
            if($res['code'] == 0){
                 $cantseegoods = $res['data'];
            }
        }

        $params['cantseegoods'] = json_encode($cantseegoods);
        $params = self::encode($params);
        $data = myhelper::sendRequest($params,'GET',true,SHOP_API_URL.'goods/list');
        if($data && isset($data['code'])){
            if($data['code'] == 0){
                return  $data['data'];
            }
        }
        return false;
    }

    //筛选条件
    public static function ListFilter($category_id,$shop_id = SHOP_ID,$spec='',$price_range=''){
        $params = self::encode(['category_id'=>$category_id,'shop_id'=>$shop_id,'selected_spec'=>$spec,'selected_price'=>$price_range]);
        $data = myhelper::sendRequest($params,'GET',true,SHOP_API_URL.'goods/category-list-filter');
        if($data && isset($data['code'])){
            if($data['code'] == 0){
                return  $data['data'];
            }
        }
        return false;
    }

    //所有分类
    public static function Categories($shop_id = SHOP_ID){
        $params = ['shop_id'=>$shop_id,'lang'=>LANG_SET];
        $params = self::encode($params);
        $data = myhelper::sendRequest($params,'GET',true,SHOP_API_URL.'goods/category');
        if($data && isset($data['code'])){
            if($data['code'] == 0){
                return  $data['data'];
            }
        }
        return false;
    }

    //收藏商品动作
    public static function Favorite($goods_id=0,$uid=0){
        $params = [
            'goods_id'=>$goods_id,
            'uid'=>$uid
        ];
        $params = self::encode($params);
        $data = myhelper::sendRequest($params,'GET',true,SHOP_API_URL.'goods/favorite');
        if($data && isset($data['code'])){
            if($data['code'] == 0){
                return  $data;
            }
        }
        return false;
    }

    //我的收藏商品
    public static function MyFavorite($orderBy,$lang,$page,$uid,$shop_id,$per_page){
        //wdx 0710
        $ips = YII::$app->request->get('ip', '');//'23.10.1.63';//
        $getip = '';//\Yii::$app->getRequest()->getUserIP();
        if($ips)  $getip = $ips;
        $cityname = self::getLocationInfoByIp($getip);

        $cantseegoods = [];
        if($cityname && in_array($cityname,\yii::$app->params['goods_cantsee_countrys'])){
            $cantseegoods = \yii::$app->params['goods_cantsee_ids'];
        }
        $params = [
            'uid'=>$uid,
            'order'=>$orderBy,
            'lang'=>$lang,
            'page'=>$page,
            'per_page'=>$per_page,
            'shop_id'=>$shop_id,
            'cantseegoods'=>json_encode($cantseegoods)
        ];
        $params = self::encode($params);
        $data = myhelper::sendRequest($params,'GET',true,SHOP_API_URL.'goods/my-favorite');
        if($data && isset($data['code'])){
            if($data['code'] == 0){
                return  $data['data'];
            }
        }
        return false;
    }

    //专题页
    public static function Special($id,$cid,$lang,$page,$per_page,$uid){
        $params = [
            'id'=>$id,
            'cid'=>$cid,
            'lang'=>$lang,
            'page'=>$page,
            'per_page'=>$per_page,
            'uid'=>$uid
        ];
        $params = self::encode($params);
        $data = myhelper::sendRequest($params,'GET',true,SHOP_API_URL.'goods/special');
        if($data && isset($data['code'])){
            if($data['code'] == 0){
                return  $data['data'];
            }
        }
        return false;
    }

    //评论动作
    public static function Comment($goods_id,$shop_id,$content,$star,$picture,$uid,$uname,$avatar){
        $params = [
            'goods_id'=>$goods_id,
            'shop_id'=>$shop_id,
            'content'=>$content,
            'star'=>$star,
            'picture'=>$picture,
            'uid'=>$uid,
            'uname'=>$uname,
            'avatar'=>$avatar
        ];
        $params = self::encode($params);
        $data = myhelper::sendRequest($params,'GET',true,SHOP_API_URL.'comment/comment');
        if($data){
            return $data;
        }
        return false;
    }

    //单个商品的评论
    public static function CommentList($goods_id,$shop_id,$page,$per_page,$uid){
        $params = [
            'goods_id'=>$goods_id,
            'shop_id'=>$shop_id,
            'page'=>$page,
            'per_page'=>$per_page,
            'uid'=>$uid
        ];
        $params = self::encode($params);
        $data = myhelper::sendRequest($params,'GET',true,SHOP_API_URL.'comment/comment-list');
        if($data && isset($data['code'])){
            if($data['code'] == 0){
                return  $data;
            }
        }
        return false;
    }

    //首页推荐位
    public static function get_block_data($block_id,$num){
        $params = ['id'=>$block_id,'num'=>$num];
        $params = self::encode($params);
        $data = myhelper::sendRequest($params,'GET',true,SHOP_API_URL.'goods/block');
        if($data && isset($data['code'])){
            if($data['code'] == 0){
                return  $data['data'];
            }
        }
        return false;
    }

    public static function multi_get_block_data($block_ids,$num,$lang){
        $cacheKey = CACHE_PREFIX.__FILE__.__FUNCTION__."_BLOCK_DATA_".json_encode($block_ids)."_".$num."_".$lang;
        if ($data = \Yii::$app->redis->get($cacheKey)) {
            return unserialize($data);
        }else{
            $params = ['id'=>json_encode($block_ids),'num'=>$num,'lang'=>$lang,];
            $params = self::encode($params);
            $data = myhelper::sendRequest($params,'GET',true,SHOP_API_URL.'goods/block-batch');
            if($data && isset($data['code'])){
                if($data['code'] == 0){
                    $data_ = isset($data['data'])?$data['data']:'';
                    \Yii::$app->redis->set($cacheKey, serialize($data_));
                    \Yii::$app->redis->expire($cacheKey,CACHE_EXPIRE);
                    return  $data_;
                }
            }
        }
        return false;
    }

    public static function DelGoodDetailCache($goods_id){
        $params = ['id'=>$goods_id];
        $params = self::encode($params);
        $data = myhelper::sendRequest($params,'GET',true,SHOP_API_URL.'update-cache/del-good-detail-cache');
        if($data && isset($data['code'])){
            if($data['code'] == 0){
                return  true;
            }
        }
        return false;
    }


    public static function DelCommentCache($goods_id){
        $params = ['good_id'=>$goods_id,'shop_id'=>SHOP_ID];
        $params = self::encode($params);
        $data = myhelper::sendRequest($params,'GET',true,SHOP_API_URL.'update-cache/del-comment-cache');
        if($data && isset($data['code'])){
            if($data['code'] == 0){
                return  true;
            }
        }
        return false;
    }


    public static function DelGoodsListCache($category_id){
        $params = ['category_id'=>$category_id];
        $params = self::encode($params);
        $data = myhelper::sendRequest($params,'GET',true,SHOP_API_URL.'update-cache/del-goods-list-cache');
        if($data && isset($data['code'])){
            if($data['code'] == 0){
                return  true;
            }
        }
        return false;
    }



    public static function CartCount($goods_id){
        $params = [
            'goods_id'=>$goods_id,
        ];
        $params = self::encode($params);
        $data = myhelper::sendRequest($params,'GET',true,SHOP_API_URL.'goods/cart-count');
        if($data){
            return $data;
        }
        return false;
    }



    public static function ShowColor(){
        $params = self::encode([]);
        $data = myhelper::sendRequest($params,'GET',true,SHOP_API_URL.'goods/show-color');
        if($data && isset($data['code'])){
            if($data['code'] == 0){
                return  $data['data'];
            }
        }
        return false;
    }
     //wdx 0711
    public static function getLocationInfoByIp($ips='')
    {
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
        if($ips) $ip= $ips;
        $ip_data = @json_decode
        (file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
        if ($ip_data && $ip_data->geoplugin_countryName != null) {
            return $ip_data->geoplugin_city.' '.$ip_data->geoplugin_region.' '.$ip_data->geoplugin_timezone;
        }

        return  '';
    }
    public static function getRegionCmpare($city,$cantseecitys){
        if($city && $cantseecitys && is_array($cantseecitys)){

            foreach ($cantseecitys as $key => $value) {
                if($value){
                    if(strstr($city,$value)){
                        return $key;
                    }
                }
            }
        } 
        return -1; 
    }
}