<?php
namespace app\modules\wedding\controllers;

use app\helpers\myhelper;
use app\modules\wedding\services\Cart;
use \app\modules\shop\models\GoodsCart;
use Yii;
use yii\helpers\ArrayHelper;
use app\Library\ShopPay\ShopPay;

class CartController extends CommonController
{
    public $defaultAction = 'index';
    private $maxItemNumber;
    private $maxCountItemNumber;


    public function init()
    {
        parent::init();
        $this->layout = '@module/views/' . GULP . '/public/main-shop.html';
        $this->maxItemNumber = MAX_CART_ITEM_NUMBER;
        $this->maxCountItemNumber = MAX_CART_COUNT_ITEM_NUMBER;


        $bread[] = [
            'url'=>'/cart/index',
            'name'=>  \YII::t('shop','Cart')
        ];
        $this->view->params['bread'] = $bread;
    }

    public function actionAddItem()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        try {
            $shop_id = $this->getShopId();
            $uid     = $this->getUid();
            $id      = (int) Yii::$app->request->post( 'item_id', 0 );
            $number  = (int) Yii::$app->request->post( 'number', 1 );
            $ads = $this->cookies->get("ads","") ? $this->cookies->get("ads","")->value : "";
            $temp_uid = 0;
            if($this->cookies->has("temp_uid"))   
            $temp_uid =$this->cookies->get("temp_uid","")->value;
            $http_referer = '';
            if($this->cookies->has("http_referer"))   
            $http_referer =$this->cookies->get("http_referer","")->value;
            
            $cartService = new Cart();
            $goodsSku = $cartService->goodsSku($id);

            //{"Bust":"20 cm","Waist":"20 cm","Hips":"20 cm","Hollow to Floor":"20 cm","Biceps Circumference":"20 cm","Sleeve Length":"20 cm","Heel Height":"20 cm"}
            if(array_intersect(explode(",",CUSTOM_ID),$goodsSku['spec_value_ids'])){
                $custom_size =  Yii::$app->request->post( 'custom_size', '');
                $custom_size = myhelper::check_custom_size($custom_size);
                if(!$custom_size){
                    return [ 'code' => 19000, 'message' => 'Custom size is invalid', 'data' => ''];
                }
            }else{
                $custom_size = NULL;
            }

            if (!$uid) {
                //未登录加入购物车:先查询COOKIE有没有
                $result =  $this->addCookieCart($shop_id, $id, $number,$custom_size,$ads);
                if($temp_uid){
                    $cartService = new Cart();
                    $result = $cartService->addCartTemp($shop_id, $id, $number, 0,$custom_size,$ads,$temp_uid,$http_referer,'pc');
                }
              
            } else {
                // 已登陆直接更新购物车
                $cartService = new Cart();
                $result = $cartService->addCart($shop_id, $id, $number, $uid,$custom_size,$ads,$http_referer,'pc');
                Yii::$app->redis->del(CACHE_PREFIX . "_cart_num_" . $uid);
            }
            return $result;

        } catch (\Exception $error) {
            // todo: log
            return [ 'code' => 19000, 'message' => 'unknown error', 'data' => ''];
        }
    }


    public function actionDeleteItem()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        try {
            $shop_id = $this->getShopId();
            $uid     = $this->getUid();
            $id      = (int) Yii::$app->request->post( 'id', 0 );

            if(!$uid){
                //未登录下 删除cookie购物车
                return $this->deleteCookieCart($shop_id, $id);
            }else{
                // 已登陆直接更新购物车
                $cartService = new Cart();
                $result = $cartService->deleteCart($shop_id, $id, $uid);
                Yii::$app->redis->del(CACHE_PREFIX . "_cart_num_" . $uid);
                return $result;
            }
        } catch (\Exception $e) {
            return [ 'code' => 19000, 'message' => 'unknown error', 'data' => ''];
        }
    }


    public function actionGetItemNumber() {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $this->check_user('',true);

        $number = $this->getCartNumber();
        return [ 'code' => 0, 'message' => '', 'data' => ['number' => $number] ];
    }

    public function getCartNumber()  {
        $shop_id = $this->getShopId();
        $uid     = $this->getUid();

        $number = 0;
        if(!$uid){
            $cookie_read = \YII::$app->request->cookies;
            if($cookie_read->has('cart')){
                $cookie_cart = $cookie_read->get('cart');
                $cookie_cart = unserialize($cookie_cart->value);
                if($cookie_cart)
                    $number = count($cookie_cart);
            }
        }else{
            $cartService = new Cart();
            $number      = $cartService->cartCount($shop_id, $uid);
        }
        return $number;
    }

    public function actionUpdateItem() {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        try {
            $shop_id = $this->getShopId();
            $uid     = $this->getUid();
            $id      = (int) Yii::$app->request->post( 'id', 0 );
            $number  = (int) Yii::$app->request->post( 'number', 1 );

            if(!$uid){
                return $this->updateCookieCart($id, $number);
            }else{
                // 已登陆直接更新购物车
                $cartService = new Cart();
                $result = $cartService->updateCart($shop_id, $id, $number, $uid);
                Yii::$app->redis->del(CACHE_PREFIX . "_cart_num_" . $uid);
                return $result;
            }
        } catch (\Exception $e) {
            return [ 'code' => 19000, 'message' => 'unknown error', 'data' => ''];
        }
    }

    public function actionIndex()
    {
        $shop_id = $this->getShopId();
        $uid     = $this->getUid();
        $lang    = $this->getLang();

        $cartService = new Cart();
        if(!$uid){
            //未登录状态浏览cookie购物车
            $items = [];
            $cookie_read = \YII::$app->request->cookies;
            if($cookie_read->has('cart')){
                $cookie_cart = $cookie_read->get('cart');
                $cookie_cart  = unserialize(gzdecode($cookie_cart->value));
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
        //var_dump($promotion);exit;
        $_result = [];
        if ($result) {
            foreach ($result as $key => $value) {
                $_value['item_id'] = $value['item_id'];
                $_value['number'] = $value['number'];
                $_value['custom_size'] = $value['custom_size'];
                $_value['gtm_id'] = $value['gtm_id'];
                $_value['gtm'] = $value['gtm'];
                $_result[$value['item_id']] = $_value;
            }
        }
        $shopPay = new ShopPay('bycouturier');
       // echo json_encode($_result);exit;
        $product_info = $shopPay->adap('shop', 'product-info', ['items' => json_encode($_result), 'custom_id' => CUSTOM_ID,'lang'=>LANG_SET]);

        $list = myhelper::productSort2($product_info['data']['product']);
        
         $jsonlist = json_encode($list);//wdx 0606
          //var_dump($jsonlist);
         $shopPay = new ShopPay(3);
         $activity_list = $shopPay->ActivityInfo($jsonlist);//wdx 0606
         $actlist = [];
         if($activity_list['code'] == 0 && isset($activity_list['data']))
            $actlist = $activity_list['data'];
          //var_dump($actlist);
        $act_products = [];
        $aff_products = [];
        $act_info = [];
        $re_price = 0;
        $re_price2 = 0;
        if($actlist){
            
            foreach ($actlist as $key => $value) {
                if(isset($value[2])){
                    $act_info = $value[2];
                    // if(isset($value[2]['re_price']))
                    // $re_price = round($value[2]['re_price']*THINK_RATE_M, 3);
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
        if($list){
            foreach ($list as $key => $item) {
                if($item){
                     $price = $item['price'];
                     $total += $price;
                     if($act_products){
                        foreach ($act_products as $k => $val) {
                            //var_dump($k);var_dump($item['id']);
                            if($k == $item['id']){
                                if($act_info)
                                $list[$key]['activity_info'] = $act_info['name'];
                            }
                        }
                     }
                     if($aff_products){
                        foreach ($aff_products as $k => $val) {
                            //var_dump($k);var_dump($item['id']);
                            if($k == $item['id']){
                                if($act_info){
                                     $tempprice =  round($price*(1-$act_info['affected_discount'])*THINK_RATE_M,3) ;
                                     $re_price2 +=$tempprice;
                                     //$tempprice =  round($tempprice,2) ;
                                     $list[$key]['activity_info'] = $act_info['name'].' <span style="float:right;font-size:20px;font-family: gara;"> -'.$tempprice.' </span>';
                                }
                               
                            }
                        }
                     }
                }
            }
            $total = $total_ori =  round($total*THINK_RATE_M, 2);
           
            $re_price = $re_price?$re_price:$re_price2;
            $total -=$re_price;
            $total =  round($total, 2);
        }   
        return $this->render('cart.html', [
            "list" => $list,
            'promotion' => $promotion,
            'actlist'=>$actlist,
            'total'=>$total,
            'total_ori'=>$total_ori,
            're_price'=>$re_price
        ]);
    }

    /**
     * 获取用户id
     *
     * @return int
     */
    private function getUid()
    {
        return isset($this->user_info['id'])?$this->user_info['id']:null;
    }

    /**
     * 获取商店id
     *
     * @return int
     */
    private function getShopId()
    {
        // todo:
        return SHOP_ID;
    }

    /**
     * 获取语言
     *
     * @return string
     */
    private function getLang()
    {
        // todo:
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
            $cartItem->region_id = REGION_ID;
            $cartItem->ads = $ads;
            $cartItem->custom_size = $custom_size;
            $cartItem->created_time = $cartItem->updated_time = time();
            $cookie_cart[$item_id] = $cartItem;
            
            // $cartItem = [];
            // $cartItem['shop_id'] = $shop_id;
            // $cartItem['item_id'] = $item_id;
            // $cartItem['price'] = $price;
            // $cartItem['number'] = $number;
            // $cartItem['custom_size'] = $custom_size;
            // $cartItem['created_time'] = $cartItem['updated_time'] = time();
            // $cookie_cart[$item_id] = $cartItem;
            

        };

        // $add_number = 0;
        $cookie_cart = [];
        if ($cookie_read->has('cart')) {
            $cookie_cart = $cookie_read->getValue('cart');
            $cookie_cart = unserialize(gzdecode($cookie_cart));
            if (!isset($cookie_cart[$item_id])) {
                // $add_number = 1;
                $func($sku['price'], $cookie_cart);
            } else {
                $cookie_cart[$item_id]->number += $number;
            }
        } else {
            // $add_number = 1;
            $func($sku['price'], $cookie_cart);
        }

        $cookie->add(new \yii\web\Cookie([
            'name'  => 'cart',
            'value' => gzencode(serialize($cookie_cart)),
            'expire'=> time() + 86400,
            'domain'=>\YII::$app->params['COOKIE_DOMAIN'],
        ]));

        $_cartCount = count($cookie_cart);
        $cartCount = 0;
        if (is_array($cookie_cart)) {
            foreach ($cookie_cart as $key => $value) {
                $cartCount += $value['number'];
            }
        }
        
        $gtm = $cartService->gtm($item_id, $_cartCount);

        return ['code' => 0, 'message' => '', 'data' => ['add_number' => $number, 'cart_item_count' => $cartCount, 'gtm'=>json_encode([$gtm])]];
    }

    private function deleteCookieCart($shop_id, $item_id)
    {
        $cookie = \YII::$app->response->cookies;
        $cookie_read = \YII::$app->request->cookies;
        $temp_uid = 0;
        $cookie_cart = [];
        if($cookie_read->has('cart')){
            $cookie_cart = $cookie_read->get('cart');
            $cookie_cart = unserialize(gzdecode($cookie_cart->value));
            if(isset($cookie_cart[$item_id])){
                //数量减1
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
                'value'  => gzencode(serialize($cookie_cart)),
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
            $cookie_cart = unserialize($cookie_cart->value);
            if(isset($cookie_cart[$item_id])){
                $cookie_cart[$item_id]->number = $number;
                $cookie_cart[$item_id]->price = $sku['price'];
                $cookie_cart[$item_id]->updated_time = time();

                $cookie->add(new \yii\web\Cookie([
                    'name'   => 'cart',
                    'value'  => serialize($cookie_cart),
                    'expire' => time()+86400,
                    'domain' => \YII::$app->params['COOKIE_DOMAIN'],
                ]));
            }
        }
        return [ 'code' => 0, 'message' => '', 'data' => [ 'cart_item_count' => $this->getCartNumber()]];
    }

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




    public function actionInitCookieCart(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $cookie_read = YII::$app->request->cookies;
        if($cookie_read->has('cart')) {
            $cart = $cookie_read->get('cart');
            $cart_models = $cart->value;
            $cartService = new Cart();
            $ref = $cartService->init_cookie_cart($this->uid,$cart_models,SHOP_ID);
            if($ref){
                YII::$app->response->cookies->remove($cart);
                return [ 'code' => 0, 'message' => 'init cookie cart ok'];
            }else{
                return [ 'code' => 1, 'message' => 'init cookie cart error'];
            }
        }else{
            return [ 'code' => 1, 'message' => 'no cookie cart'];
        }
    }

    public function actionGetNum(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $cartService = new Cart();
        $num = $cartService->CartNum($this->uid,YII::$app->request->cookies);
        return [ 'code' => 0, 'message' => 'init cart num ok', 'data' => $num];
    }

}
