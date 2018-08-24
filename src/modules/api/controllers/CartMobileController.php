<?php
namespace app\modules\api\controllers;

use \app\modules\api\models\GoodsCart;
use app\modules\api\services\Cart;
use Yii;
use app\Library\Mlog;
use app\Library\ShopPay\ShopPay;
use app\helpers\myhelper;
// todo: add 和 update需要考虑库存
class CartMobileController extends CommonController
{

    // 最大的存储条数
    private $maxItemNumber;
    private $maxCountItemNumber;

    public function init()
    {
        parent::init();
        parent::behaviors();
        $this->maxItemNumber = MAX_CART_ITEM_NUMBER;
        $this->maxCountItemNumber = MAX_CART_COUNT_ITEM_NUMBER;
        $this->enableCsrfValidation = false;
        
        $mlog = new Mlog();
        $mlog->inlog('cart', 'init', $this->user_info);

        if(!$this->is_login){
            return $this->result(10001,[],'please log in first!');
        }
    }

    public function actionAddItem()
    {
        try {
            $shop_id = $this->getShopId();
            $uid     = $this->getUid();
            $id     = (int) Yii::$app->request->post( 'item_id', 0 );
            $number = (int) Yii::$app->request->post( 'number', 1 );
            $ads = $this->cookies->get("ads","") ? $this->cookies->get("ads","")->value : "";
            $cartService = new Cart();
            $goodsSku = $cartService->goodsSku($id);
            $temp_uid =0;
            if($this->cookies->has("temp_uid"))
            $temp_uid =$this->cookies->get("temp_uid","")->value;
            $http_referer = '';
            if($this->cookies->has("http_referer"))
            $http_referer =$this->cookies->get("http_referer","")->value;
   
            //{"Bust":"20 cm","Waist":"20 cm","Hips":"20 cm","Hollow to Floor":"20 cm"}
            if(array_intersect(explode(",",CUSTOM_ID),$goodsSku['spec_value_ids'])){
                $custom_size =  Yii::$app->request->post( 'custom_size', '');
                if(!$custom_size){
                    return [ 'code' => 19000, 'message' => 'custom_size is empty', 'data' => ''];
                }
            }else{
                $custom_size = NULL;
            }
         
            if (!$uid) {
                // @TODO 未登录加入购物车:先查询COOKIE有没有，暂不支持
                $result = $this->addCookieCart($shop_id, $id, $number,$custom_size,$ads);
                if($temp_uid){
 
                    $cartService = new Cart();
                    $result = $cartService->addCartTemp($shop_id, $id, $number, 0,$custom_size,$ads,$temp_uid,$http_referer,'md');
                }
            } else {
                // 已登陆直接更新购物车
                $cartService = new Cart();
                $result = $cartService->addCart($shop_id, $id, $number, $uid,$custom_size,$ads,$http_referer,'md');
            }
            return $result;
        } catch (\Exception $error) {
            return $this->result(19000,[],'unknown error');
        }
    }

    public function actionDeleteItem()
    {
        try {
            $shop_id =  $this->getShopId();
            $uid     =  $this->getUid();
            $id      = (int) Yii::$app->request->post( 'id', 0 );
            $cart_id      =  (int) Yii::$app->request->post( 'cart_id', 0 );
            if(!$uid){
                //未登录下 删除cookie购物车，暂不支持
                $result = $this->deleteCookieCart($shop_id, $id);
            }else{
                // 已登陆直接更新购物车
                $cartService = new Cart();
                $result = $cartService->deleteCart($shop_id, $id, $uid,$cart_id);
            }
            return $result;
        } catch (\Exception $e) {
            return $this->result(19000,[],'unknown error');
        }
    }

    public function actionUpdateItem()
    {
        try {
            $shop_id = $this->getShopId();
            $uid     = $this->getUid();
            $id      = (int) Yii::$app->request->post( 'id', 0 );
            $number  = (int) Yii::$app->request->post( 'number', 1 );

            if(!$uid){
                // @TODO 未登录用户，读取cookie，暂不支持
                $result = $this->updateCookieCart($id, $number);
            }else{
                // 已登陆直接更新购物车
                $cartService = new Cart();
                $result = $cartService->updateCart($shop_id, $id, $number, $uid);
            }
            return $result;
        } catch (\Exception $e) {
            return $this->result(19000,[],'unknown error');
        }
    }

    public function actionGetCart()
    {
        try {
            $shop_id = $this->getShopId();
            $uid     = $this->getUid();
          
            $lang    = Yii::$app->request->get('lang','en-us');
 
            $cartService = new Cart();
            if(!$uid){
                //未登录状态浏览cookie购物车
                $items = [];
                $cookie_read = \YII::$app->request->cookies;
                if($cookie_read->has('cart')){
                    $cookie_cart = $cookie_read->get('cart');  
                    $cookie_cart  = unserialize($cookie_cart->value);
                    foreach ($cookie_cart as $id => $c_cart){
                        $items[$id] = $c_cart->toArray();
                    }
                }

                if (empty($items)) {
                    $items = '';
                } else {
                    $items = serialize($items);
                }
                $result = $cartService->cartCookieList($shop_id, $lang, $items);
            } else {
                $result = $cartService->cartDbList($shop_id, $lang, $uid);
            }
            // 免运费
            $promotion = $cartService->goodsPromotion();

            $_result = [];
            if ($result) {
                foreach ($result as $key => $value) {
                    if(isset($value['number']) && $value['number'] > 0){
                        $_value['item_id'] = $value['item_id'];
                        $_value['number'] = $value['number'];
                        $_value['custom_size'] = $value['custom_size'];
                        $_value['gtm_id'] = $value['gtm_id'];
                        $_value['gtm'] = $value['gtm'];
                        $_result[$value['item_id']] = $_value;
                    }
                   
                }
            }
            $shopPay = new ShopPay('bycouturier');
            $product_info = $shopPay->adap('shop', 'product-info', ['items' => json_encode($_result), 'custom_id' => CUSTOM_ID,'lang'=>LANG_SET]);

            $list = myhelper::productSort2($product_info['data']['product']);
            $jsonlist = json_encode($list);//wdx 0606
              //var_dump($jsonlist);
            $shopPay = new ShopPay(3);
            $activity_list = $shopPay->ActivityInfo($jsonlist);//wdx 0606
            $actlist = [];
            if($activity_list['code'] == 0 && isset($activity_list['data'])){
                $actlist = $activity_list['data'];
            }
            
            $act_products = [];
            $aff_products = [];
            $act_info = [];
            $re_price = 0;
            if($actlist){
                
                foreach ($actlist as $key => $value) {
                    if(isset($value[2])){
                        $act_info = $value[2];
                        if(isset($value[2]['re_price']))
                        $re_price += $value[2]['re_price'];
                    }
                    if(isset($value[0])){
                        $act_products = $value[0];//array_merge($act_products,$value[0]);
                    }
                     if(isset($value[1])){
                        $aff_products = $value[1];//array_merge($aff_products,$value[1]);
                    }
                    
                }
               
            }
             
            $total_ori = 0;
            $total = 0;
            if($result ){
                foreach ($result as $key => $item) {
                    if($item){
                         $price = $item['price'];
                         $total += $price;
                         if($act_products){
                            foreach ($act_products as $k => $val) {
                                 if($k == $item['item_id']){
                                    if($act_info)
                                    $result[$key]['activity_info'] = $act_info['name'];
                                }
                            }
                         }
                         if($aff_products){
                            foreach ($aff_products as $k => $val) {
                                 if($k == $item['item_id']){
                                    if($act_info){
                                         $tempprice = $price*(1-$act_info['affected_discount']);
                                         $result[$key]['activity_info'] = $act_info['name'];
                                         $result[$key]['activity_amount'] = $tempprice;
                                    }
                                   
                                }
                            }
                         }
                    }
                }
                // $total = $total_ori =  round($total*THINK_RATE_M, 2);

                // $re_price = round($re_price*THINK_RATE_M, 2);
                // $total -=$re_price;
                
            }


            return $this->result(0, $result,'');

        } catch (\Exception $e) {
            return $this->result(19000,[],'unknown error');
        }
    }
    //wdx
    public function actionGetCartGuest()
    {
        try {
            $shop_id = $this->getShopId();
            $uid     = $this->getUid();
          
            $lang    = Yii::$app->request->get('lang','en-us');
            $items = Yii::$app->request->post( 'items',[]);
            if(!$items) 
               $items = Yii::$app->request->get('items',[]);
            //$items = '{"384096":{"item_id":384096,"number":1,"custom_size":{},"price":"329.95"}}';
            //$items = '{"384096":{"item_id":384096,"number":1,"custom_size":{"unit":"inch","Bust":"1","Under Bust":"1","Waist":"1","Waist To Floor":"1","Hips":"1","Hollow To Floor":"1","Back Shoulder Width":"1","Arm Circumference":"1","Arm Eye Circumference":"1","Mid-Shoulder to Bust Point":"1","Arm Length":"1","Heel Height":"1","Height":"1"},"price":"269.00"}}';
            if($items){
                $items = json_decode($items,true);
            }
            
            $items = serialize($items);
 
            //$items = 'a:2:{i:15655;a:9:{s:7:"shop_id";i:3;s:7:"item_id";i:15655;s:5:"price";s:6:"399.00";s:6:"number";i:1;s:9:"region_id";s:3:"235";s:3:"ads";s:0:"";s:11:"custom_size";N;s:12:"updated_time";i:1529490125;s:12:"created_time";i:1529490125;}i:33428;a:9:{s:7:"shop_id";i:3;s:7:"item_id";i:33428;s:5:"price";s:6:"109.00";s:6:"number";i:1;s:9:"region_id";s:3:"235";s:3:"ads";s:0:"";s:11:"custom_size";N;s:12:"updated_time";i:1529490262;s:12:"created_time";i:1529490262;}}';
            $cartService = new Cart();
             
            $result = $cartService->cartCookieList($shop_id, $lang, $items);
             //var_dump($result);exit;
            // 免运费
            $promotion = $cartService->goodsPromotion();
           
            $_result = [];
            if ($result) {
                foreach ($result as $key => $value) {
                    if(isset($value['number']) && $value['number'] > 0){
                        $_value['item_id'] = $value['item_id'];
                        $_value['number'] = $value['number'];
                        $_value['custom_size'] = $value['custom_size'];
                        $_value['gtm_id'] = $value['gtm_id'];
                        $_value['gtm'] = $value['gtm'];
                        $_result[$value['item_id']] = $_value;
                    }
                   
                }
            }
            $shopPay = new ShopPay('bycouturier');
            $product_info = $shopPay->adap('shop', 'product-info', ['items' => json_encode($_result), 'custom_id' => CUSTOM_ID,'lang'=>LANG_SET]);

            $list = myhelper::productSort2($product_info['data']['product']);
            $jsonlist = json_encode($list);//wdx 0606
              //var_dump($jsonlist);
            $shopPay = new ShopPay(3);
            $activity_list = $shopPay->ActivityInfo($jsonlist);//wdx 0606
            $actlist = [];
            if($activity_list['code'] == 0 && isset($activity_list['data'])){
                $actlist = $activity_list['data'];
            }
            
            $act_products = [];
            $aff_products = [];
            $act_info = [];
            $re_price = 0;
            if($actlist){
                
                foreach ($actlist as $key => $value) {
                    if(isset($value[2])){
                        $act_info = $value[2];
                        if(isset($value[2]['re_price']))
                        $re_price += $value[2]['re_price'];
                    }
                    if(isset($value[0])){
                        $act_products = $value[0];//array_merge($act_products,$value[0]);
                    }
                     if(isset($value[1])){
                        $aff_products = $value[1];//array_merge($aff_products,$value[1]);
                    }
                    
                }
               
            }
             
            $total_ori = 0;
            $total = 0;
            if($result ){
                foreach ($result as $key => $item) {
                    if($item){
                         $price = $item['price'];
                         $total += $price;
                         if($act_products){
                            foreach ($act_products as $k => $val) {
                                 if($k == $item['item_id']){
                                    if($act_info)
                                    $result[$key]['activity_info'] = $act_info['name'];
                                }
                            }
                         }
                         if($aff_products){
                            foreach ($aff_products as $k => $val) {
                                 if($k == $item['item_id']){
                                    if($act_info){
                                         $tempprice =  $price*(1-$act_info['affected_discount']);
                                         $result[$key]['activity_info'] = $act_info['name'];
                                         $result[$key]['activity_amount'] = $tempprice;
                                    }
                                   
                                }
                            }
                         }
                    }
                }
                // $total = $total_ori =  round($total*THINK_RATE_M, 2);

                // $re_price = round($re_price*THINK_RATE_M, 2);
                // $total -=$re_price;
                
            }


            return $this->result(0, $result,'');

        } catch (\Exception $e) {
            return $this->result(19000,[],'unknown error');
        }
    }


    public function actionGetItemNumber()
    {
        $number = $this->getCartNumber();
        return $this->result(0, ['number' => $number],'');
    }

    public function getCartNumber()  {
        $shop_id = $this->getShopId();
        $uid     = $this->getUid();

        $number = 0;
        if(!$uid){
            // @TODO 未登录用户，读取cookie，暂不支持
            $cookie_read = \YII::$app->request->cookies;
            if($cookie_read->has('cart')){
                $cookie_cart = $cookie_read->get('cart');
                $cookie_cart = unserialize(gzdecode($cookie_cart->value));
                if($cookie_cart)
                    $number = count($cookie_cart);
            }
        }else{
            $cartService = new Cart();
            $number      = $cartService->cartCount($shop_id, $uid);
        }
        return $number;
    }

    /* # todo */
    public function getShopId()
    {
        return SHOP_ID;
    }

    public function getUid()
    {
        return isset($this->user_info['id']) ? $this->user_info['id'] : null;
    }

    public function getLang()
    {
        return LANG_SET;
    }

    private function addCookieCart($shop_id, $item_id, $number,$custom_size,$ads)
    {
        if ($number <= 0) {
            return [ 'code' => 19002, 'message' => Yii::t('shop','number is too small'), 'data' => []];
        }

        $cartService = new Cart();
        $goodsSku = $cartService->goodsSku($item_id);
        $sku   = $goodsSku['sku'];
        $goods = $goodsSku['goods'];

        if (empty($sku)) {
            return [ 'code' => 19001, 'message' => Yii::t('shop','NoSku'), 'data' => []];
        }

        if ( empty($goods) || $goods['status'] == 0 || $sku['status'] == 0 ) {
            return [ 'code' => 19003, 'message' => Yii::t('shop','SoldOut'), 'data' => []];
        }

        $cookie = \YII::$app->response->cookies;
        $cookie_read = \YII::$app->request->cookies;

        /**
         * 构建购物车数据集
         */
        $func = function ($price, &$cookie_cart) use($item_id, $shop_id, $number,$custom_size,$ads) {
            $cartItem = new GoodsCart();
            $cartItem->shop_id = $shop_id;
            $cartItem->item_id = $item_id;
            $cartItem->price   = $price;
            $cartItem->number  = $number;
            $cartItem->custom_size  = $custom_size;
            $cartItem->ads  = $ads;
            $cartItem->created_time = $cartItem->updated_time = time();
            $cookie_cart[$item_id] = $cartItem;
        };

        $add_number = 0;
        $cookie_cart = [];
        if ($cookie_read->has('cart')) {
            $cookie_cart = $cookie_read->getValue('cart');
            $cookie_cart = unserialize(gzdecode($cookie_cart));
            if (!isset($cookie_cart[$item_id])) {
                $add_number = 1;
                $func($sku['price'], $cookie_cart);
            } else {
                $cookie_cart[$item_id]->number += $number;
            }
        } else {
            $add_number = 1;
            $func($sku['price'], $cookie_cart);
        }

        $cookie->add(new \yii\web\Cookie([
            'name'  => 'cart',
            'value' => gzencode(serialize($cookie_cart)),
            'expire'=> time() + 86400,
            'domain'=>\YII::$app->params['COOKIE_DOMAIN'],
        ]));

        $cartCount = count($cookie_cart);
        $gtm = $cartService->gtm($item_id, $cartCount);

        return ['code' => 0, 'message' => '', 'data' => ['add_number' => $add_number, 'cart_item_count' => $cartCount, 'gtm'=>json_encode([$gtm])]];
    }

    private function deleteCookieCart($shop_id, $item_id)
    {
        $cookie = \YII::$app->response->cookies;
        $cookie_read = \YII::$app->request->cookies;
        $temp_uid = 0;
        $cookie_cart = [];
        if($cookie_read->has('cart')){
            $cookie_cart = $cookie_read->get('cart');
            $cookie_cart = unserialize(gzdecode($cookie_cart));
            if(isset($cookie_cart[$item_id])){
               if ($cookie_cart[$item_id]['number'] > 0) {
                    $cookie_cart[$item_id]['number'] -= 1;
                } else{
                    unset($cookie_cart[$item_id]);
                }
                if($cookie_read->has("temp_uid"))
                $temp_uid =$cookie_read->get("temp_uid")->value;
                if($temp_uid){
                    $cartService = new Cart();
                    $result = $cartService->deleteCartTemp($shop_id, $item_id, $temp_uid);
                }
            }
            $cookie->add(new \yii\web\Cookie([
                'name'   => 'cart',
                'value' => gzencode(serialize($cookie_cart)),
                'expire' => time()+86400,
                'domain' => \YII::$app->params['COOKIE_DOMAIN'],
            ]));
        }

        $cartCount = count($cookie_cart);
        $cartService = new Cart();
        $gtm = $cartService->gtm($item_id, $cartCount);

        return ['code' => 0, 'message' => '', 'data' => ['cart_item_count' => $cartCount, 'gtm'=>json_encode([$gtm])]];
    }

    private function updateCookieCart($item_id, $number)
    {
        if ($number <= 0) {
            return [ 'code' => 19002, 'message' => Yii::t('shop','number is too small'), 'data' => []];
        }

        if ( $number > $this->maxItemNumber ) {
            return [ 'code' => 19003, 'message' => Yii::t('shop','ThisItemIsLimitedTo100PurchasesPerID'), 'data' => []];
        }

        $cartService = new Cart();
        $goodsSku = $cartService->goodsSku($item_id);
        $sku   = $goodsSku['sku'];
        $goods = $goodsSku['goods'];

        if (empty($sku)) {
            return [ 'code' => 19001, 'message' => Yii::t('shop','NoSku'), 'data' => []];
        }

        if ( empty($goods) || $goods['status'] == 0 || $sku['status'] == 0 ) {
            return [ 'code' => 19003, 'message' => Yii::t('shop','SoldOut'), 'data' => []];
        }

        if ( $number > $sku['store'] ) {
            return [ 'code' => 19003, 'message' => Yii::t('shop','shop.OutOfStock'), 'data' => []];
        }

        $cookie = \YII::$app->response->cookies;
        $cookie_read = \YII::$app->request->cookies;
        if($cookie_read->has('cart')){
            $cookie_cart = $cookie_read->get('cart');
            $cookie_cart = unserialize(gzdecode($cookie_cart));
            if(isset($cookie_cart[$item_id])){
                $cookie_cart[$item_id]->number = $number;
                $cookie_cart[$item_id]->price = $sku['price'];
                $cookie_cart[$item_id]->updated_time = time();

                $cookie->add(new \yii\web\Cookie([
                    'name'   => 'cart',
                    'value' => gzencode(serialize($cookie_cart)),
                    'expire' => time()+86400,
                    'domain' => \YII::$app->params['COOKIE_DOMAIN'],
                ]));
            }
        }
        return [ 'code' => 0, 'message' => '', 'data' => [ 'cart_item_count' => $this->getCartNumber()]];
    }

    /**
     * 检查库存
     */
    public function actionCheckStore()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $items = Yii::$app->request->get('items');
        $items = json_decode(urldecode($items), true);
        $shop_id = $this->getShopId();
        $lang = $this->getLang();

        $cartService = new Cart();
        $result = $cartService->checkStore($shop_id, $lang, $items);
        return $result;
    }
}
