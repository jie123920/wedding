<?php
namespace app\modules\api\services;

use app\helpers\myhelper;

class Cart extends Service
{
    /**
     * 添加至购物车
     * @param $shop_id
     * @param $item_id
     * @param $number
     * @param $uid
     * @param $custom_size
     */
    public function addCart($shop_id, $item_id, $number, $uid,$custom_size,$ads,$http_referer='',$device='md')
    {
        $params = ['shop_id' => $shop_id, 'item_id' => $item_id, 'number' => $number, 'uid' => $uid,'custom_size'=>$custom_size,'lang'=>LANG_SET,'region_id'=>REGION_ID,'ads'=>$ads,'http_referer'=>$http_referer,'device'=>$device];
        $params = self::encode($params);
        $result = myhelper::sendRequest($params, 'POST', true, SHOP_API_URL . 'cart/add-item');
        return $result;
    }
    //wdx 20180724
    public function addCartTemp($shop_id, $item_id, $number, $uid,$custom_size,$ads,$temp_uid=0,$http_referer='',$device='md')
    {
        $params = ['shop_id' => $shop_id, 'item_id' => $item_id, 'number' => $number, 'uid' => $uid,'custom_size'=>$custom_size,'lang'=>LANG_SET,'region_id'=>REGION_ID,'ads'=>$ads,'temp_uid'=>$temp_uid,'http_referer'=>$http_referer,'device'=>$device];
        $params = self::encode($params);

        $result = myhelper::sendRequest($params, 'POST', true, SHOP_API_URL . 'cart/add-item-temp');
        return $result;
    }
    /**
     * 更新购物车
     * @param $shop_id
     * @param $item_id
     * @param $number
     * @param $uid
     */
    public function updateCart($shop_id, $item_id, $number, $uid)
    {
        $params = ['shop_id' => $shop_id, 'item_id' => $item_id, 'number' => $number, 'uid' => $uid];
        $params = self::encode($params);
        $result = myhelper::sendRequest($params, 'POST', true, SHOP_API_URL . 'cart/update-item');
        return $result;
    }

    /**
     * 删除购物车商品
     * @param $shop_id
     * @param $item_id
     * @param $uid
     */
    public function deleteCart($shop_id, $item_id, $uid,$cart_id=0)
    {
        $params = ['shop_id' => $shop_id, 'item_id' => $item_id, 'uid' => $uid,'cart_id' => $cart_id];
        $params = self::encode($params);
        $result = myhelper::sendRequest($params, 'POST', true, SHOP_API_URL . 'cart/delete-item');
        return $result;
    }
    
    //wdx 20180724
    public function deleteCartTemp($shop_id, $item_id, $uid,$device='md')
    {
        $params = ['shop_id' => $shop_id, 'item_id' => $item_id, 'temp_uid' => $uid,'device'=>$device];
        $params = self::encode($params);
        $result = myhelper::sendRequest($params, 'POST', true, SHOP_API_URL . 'cart/delete-item-temp');
        return $result;
    }
    /**
     * 数据库内购物车商品
     * @param $shop_id
     * @param $lang
     * @param $uid
     */
    public function cartDbList($shop_id, $lang, $uid)
    {
        $params = ['shop_id' => $shop_id, 'lang' => $lang, 'uid' => $uid];
        $params = self::encode($params);
        $result = myhelper::sendRequest($params, 'POST', true, SHOP_API_URL . 'cart/get-db-cart');
        return isset($result['data']) ? $result['data'] : [];
    }

    /**
     * cookie 内购物车商品
     * @param $shop_id
     * @param $lang
     * @param $uid
     * @param string $items
     * @return array
     */
    public function cartCookieList($shop_id, $lang, $items = '')
    {
        $params = ['shop_id' => $shop_id, 'lang' => $lang, 'items' => $items];
        $params = self::encode($params);
        $result = myhelper::sendRequest($params, 'POST', true, SHOP_API_URL . 'cart/get-cookie-cart');
        return isset($result['data']) ? $result['data'] : [];
    }

    /**
     * 购物车数量统计
     * @param $shop_id
     * @param $uid
     */
    public function cartCount($shop_id, $uid)
    {
        $params = ['shop_id' => $shop_id, 'uid' => $uid];
        $params = self::encode($params);
        $result = myhelper::sendRequest($params, 'POST', true, SHOP_API_URL . 'cart/get-item-number');
        return isset($result['data']['number']) ? $result['data']['number'] : 0;
    }

    /**
     * 免运费配置
     * @return array
     */
    public function goodsPromotion()
    {
        $params = self::encode([]);
        $result = myhelper::sendRequest($params, 'GET', true, SHOP_API_URL . 'cart/goods-promotion');
        return isset($result['data']) ? $result['data'] : [];
    }

    /**
     * 根据 goods_sku_id、购物车商品 number获取gtm
     * @param $goods_sku_id
     * @param $number
     * @return array
     */
    public function gtm($goods_sku_id, $number)
    {
        $params = ['goods_sku_id' => $goods_sku_id, 'number' => $number];
        $params = self::encode($params);
        $result = myhelper::sendRequest($params, 'GET', true, SHOP_API_URL . 'cart/gtm');
        return isset($result['data']['gtm']) ? $result['data']['gtm'] : [];
    }

    /**
     * 根据 goods_sku_id 获取 sku及goods信息
     * @param $goods_sku_id
     * @return array
     */
    public function goodsSku($goods_sku_id)
    {
        $params = ['goods_sku_id' => $goods_sku_id];
        $params = self::encode($params);
        $result = myhelper::sendRequest($params, 'GET', true, SHOP_API_URL . 'cart/goods-sku');
        return isset($result['data']) ? $result['data'] : [];
    }

    /**
     * 检查库存
     * @param $shop_id
     * @param $lang
     * @param $items
     */
    public function checkStore($shop_id, $lang, $items)
    {
        $items = json_encode($items);
        $params = ['shop_id' => $shop_id, 'lang' => $lang, 'items' => $items];
        $params = self::encode($params);
        $result = myhelper::sendRequest($params, 'GET', true, SHOP_API_URL . 'cart/check-store');
        return $result;
    }
}