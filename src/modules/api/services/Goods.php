<?php
namespace app\modules\api\services;
use app\helpers\myhelper;
use YII;
class Goods extends Service{
    const PER_PAGE = 40;
    //获取商品详情  PC
    public static function  get($goods_id=0,$kid=0,$rate=1,$lang='en-us'){    
        //wdx 0810 edit    
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
            'lang'=>$lang,
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

    //获取商品列表 搜索列表 赛选列表
    public static function GoodsList($params = []){
        //wdx 0810 edit
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
 
    //获取筛选条件
    public static function ListFilter($category_id,$selected_spec,$selected_price,$lang){
        $params = self::encode([
             'category_id'=>$category_id,
             'selected_spec'=>$selected_spec,
             'selected_price'=>$selected_price,
             'lang'=>$lang
            ]);
        $data = myhelper::sendRequest($params,'GET',true,SHOP_API_URL.'goods/category-list-filter');
        if($data && isset($data['code'])){
            if($data['code'] == 0){
                return  $data['data'];
            }
        }
        return false;
    }

    //获取所有分类
    public static function Categories($shop_id = SHOP_ID,$lang = 'en-us'){
        $params = [
            'shop_id'=>$shop_id,
            'lang'=>$lang
        ];
        $params = self::encode($params);
        $data = myhelper::sendRequest($params,'GET',true,SHOP_API_URL.'goods/category');
        if($data && isset($data['code'])){
            if($data['code'] == 0){
                return  $data['data'];
            }
        }
        return false;
    }

    //收藏动作
    public static function Favorite($goods_id=0,$uid=0){
        $params = [
            'goods_id'=>$goods_id,
            'uid'=>$uid,
            'shop_id'=>SHOP_ID
        ];
        $params = self::encode($params);
        $data = myhelper::sendRequest($params,'GET',true,SHOP_API_URL.'goods/favorite');
        if($data && isset($data['code'])){
            return  $data;
        }
        return false;
    }

    //我的收藏列表
    public static function MyFavorite($orderBy,$lang,$page,$uid){
        //wdx 0710
        $ips = YII::$app->request->get('ip', '');//'23.10.1.63';//
        $getip = '';//\Yii::$app->getRequest()->getUserIP();
        if($ips)  $getip = $ips;
        $cityname = self::getLocationInfoByIp($getip);

        $cantseegoods = [];
        if($cityname && in_array($cityname,\yii::$app->params['goods_cantsee_countrys'])){
            $cantseegoods = \yii::$app->params['goods_cantsee_ids'];
        }
       

        $params = ['uid'=>$uid,'order'=>$orderBy,'lang'=>$lang,'page'=>$page,'shop_id'=>3]; 
        $params['cantseegoods'] = json_encode($cantseegoods);
        $params = self::encode($params);
        $data = myhelper::sendRequest($params,'GET',true,SHOP_API_URL.'goods/my-favorite');
        if($data && isset($data['code'])){
            if($data['code'] == 0){
                return  $data['data'];
            }
        }
        return false;
    }

    //商品评论动作
    public static function Comment($goods_id,$shop_id,$content,$star,$picture,$uid){
        $params = [
            'goods_id'=>$goods_id,
            'shop_id'=>$shop_id,
            'uid'=>$uid,
            'content'=>$content,
            'star'=>$star,
            'picture'=>$picture
        ];
        $params = self::encode($params);
        $data = myhelper::sendRequest($params,'GET',true,SHOP_API_URL.'comment/comment');
        if($data && isset($data['code'])){
            if($data['code'] == 0){
                return  $data;
            }
        }
        return false;
    }

    //单个商品的评论列表
    public static function CommentList($goods_id){
        $params = ['goods_id'=>$goods_id,'shop_id'=>SHOP_ID];
        $params = self::encode($params);
        $data = myhelper::sendRequest($params,'GET',true,SHOP_API_URL.'comment/comment-list');
        if($data && isset($data['code'])){
            if($data['code'] == 0){
                return  $data;
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


    //单个商品的评论列表
    public static function SkuPhotos($kid){
        $params = ['kid'=>$kid];
        $params = self::encode($params);
        $data = myhelper::sendRequest($params,'GET',true,SHOP_API_URL.'goods/sku-photos');
        if($data && isset($data['code'])){
            if($data['code'] == 0){
                return  $data;
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