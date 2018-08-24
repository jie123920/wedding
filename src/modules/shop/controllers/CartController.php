<?php
namespace app\modules\shop\controllers;

use \app\modules\shop\models\GoodsCart;
use app\modules\shop\models\GoodsPromotion;
use \app\modules\shop\models\GoodsSku;
use \app\modules\shop\models\Goods;
use Yii;
use yii\helpers\ArrayHelper;

// todo: add 和 update需要考虑库存
class CartController extends CommonController
{
    // 最大的存储条数
    private $maxItemNumber;
    private $maxCountItemNumber;

    public function init()
    {
        parent::init();
        $this->maxItemNumber = MAX_CART_ITEM_NUMBER;
        $this->maxCountItemNumber = MAX_CART_COUNT_ITEM_NUMBER;
        $this->enableCsrfValidation = false;
    }

    /**
     * 添加商品到购物车
     * @return array
     * @author jiangkun <jiangk@mutantbox.com>
     */
    public function actionAddItem()
    {
        $id      = (int) Yii::$app->request->post( 'item_id', 0 );
        $number  = (int) Yii::$app->request->post( 'number', 1 );
        $shop_id = (int) Yii::$app->request->post( 'shop_id', 0 );
        $uid     = Yii::$app->request->post('uid', 0);

        if (!$uid) {
            return $this->result(10001,[],'Please login first!');
        }

        $sku = GoodsSku::find()->where(["goods_sku_id"  => $id])->one();
        $goods = Goods::find()->where(['id' => $sku->goods_id])->one();

        if ( $goods->status == 0 || $sku->status == 0 ) {
            return $this->result(19003,[],Yii::t('shop','SoldOut'));
        }

        if ( !isset( $sku->goods_sku_id ) ) {
            return $this->result(19001,[],Yii::t('shop','NoSku'));
        }

        if ($number <= 0) {
            return $this->result(19002,[],Yii::t('shop','number is too small'));
        }

        $transaction = Yii::$app->db_shop->beginTransaction();
        try {
            $cartItem = GoodsCart::getCartOne($shop_id, $uid, $id);
            $add_number = 0;
            if ( $cartItem ){
                $itemData = ['number' => $cartItem->number + $number, 'price'  => $sku->price];
            } else {
                $add_number = 1;
                // 如果不存在，则添加记录
                $itemData = ['shop_id' => $shop_id, 'uid' => $uid, 'item_id' => $sku->goods_sku_id, 'price' => $sku->price, 'number' => $number];

                $cartItem = new GoodsCart();
            }

            // 如果存在购物车中，则添加数量
            if ( intval($cartItem->number) + $number > $this->maxItemNumber ) {
                return $this->result(19003,[],Yii::t('shop','shop.OutOfStock'));
            }

            if ( intval($cartItem->number) + $number > $sku->store ) {
                return $this->result(19003,[],Yii::t('shop','shop.OutOfStock'));
            }

            if ($cartItem->isNewRecord) {
                $res = $cartItem->addToCart($itemData);
            } else {
                $res = $cartItem->updateCart($itemData);
            }

            if (!$res) {
                $transaction->rollBack();
            }
            $transaction->commit();

            //谷歌分析
            $gtm = Goods::getGtm($sku->goods_sku_id, $cartItem->number);

            return $this->result(0,['add_number' => $add_number, 'cart_item_count' => $this->getCartNumber($uid, $shop_id), 'gtm' => json_encode([$gtm])],'');
        } catch (\Exception $error) {
            $transaction->rollBack();
            return $this->result(19000,[],'unknown error');
        }
    }

    /**
     * 从购物车删除商品
     * @return array
     * @author jiangkun <jiangk@mutantbox.com>
     */
    public function actionDeleteItem()
    {
        $id      = (int) Yii::$app->request->post( 'item_id', 0 );
        $shop_id = (int) Yii::$app->request->post( 'shop_id', 0 );
        $uid     = Yii::$app->request->post('uid', 0);

        if (!$uid) {
            return $this->result(10001,[],'Please login first!');
        }

        $transaction = Yii::$app->db_shop->beginTransaction();
        try {
            $cartItem = GoodsCart::getCartOne($shop_id, $uid, $id);
            $gtm = '';

            if ($cartItem) {
                $cartItem->delete();
                $transaction->commit();
                //谷歌分析
                $gtm = Goods::getGtm($id, $cartItem->number);
            }

            return $this->result(0,['cart_item_count' => $this->getCartNumber($uid, $shop_id), 'gtm' => json_encode([$gtm])],'');
        } catch (\Exception $e) {
            $transaction->rollBack();
            return $this->result(19000,[],'unknown error');
        }
    }

    /**
     * 更新购物车商品数量
     * @return array
     * @author jiangkun <jiangk@mutantbox.com>
     */
    public function actionUpdateItem()
    {
        $id      = (int) Yii::$app->request->post( 'item_id', 0 );
        $number  = (int) Yii::$app->request->post( 'number', 1 );
        $shop_id = (int) Yii::$app->request->post( 'shop_id', 0 );
        $uid     = Yii::$app->request->post('uid', 0);

        if (!$uid) {
            return $this->result(10001,[],'Please login first!');
        }

        $sku = GoodsSku::find()->where(["goods_sku_id" => $id])->one();
        $goods = Goods::find()->where(['id' => $sku->goods_id])->one();

        if ( $goods->status == 0 || $sku->status == 0 ) {
            return $this->result(19003,[],Yii::t('shop','SoldOut'));
        }

        if (!isset( $sku->goods_sku_id )) {
            return $this->result(19001,[],Yii::t('shop','NoSku'));
        }

        if ($number <= 0) {
            return $this->result(19002,[],Yii::t('shop','number is too small'));
        }

        if ( $number > $this->maxItemNumber ) {
            return $this->result(19003,[],Yii::t('shop','ThisItemIsLimitedTo100PurchasesPerID'));
        }

        if ( $number > $sku->store ) {
            return $this->result(19003,[], Yii::t('shop','shop.OutOfStock'));
        }

        $transaction = Yii::$app->db_shop->beginTransaction();
        try {
            $cartItem = GoodsCart::getCartOne($shop_id, $uid, $id);

            if ( $cartItem ) {
                $itemData = ['number' => $number, 'price'  => $sku->price];
                if ($cartItem->updateCart($itemData)) {
                    $transaction->commit();
                }
                $transaction->rollBack();

                return $this->result(0,[ 'cart_item_count' => $this->getCartNumber($uid, $shop_id) ],'');
            } else {
                return $this->result(19004,[],'does not exists goods');
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            return $this->result(19000,[],'unknown error');
        }
    }

    /**
     * 获取 Cookie 端购物车列表
     * @return array
     * @author jiangkun <jiangk@mutantbox.com>
     */
    public function actionGetDbCart()
    {
        $lang    = Yii::$app->request->post('lang','en-us');
        $shop_id = (int) Yii::$app->request->post( 'shop_id', 0 );
        $uid     = Yii::$app->request->post('uid', 0);

        if (!$uid) {
            return $this->result(10001,[],'Please login first!');
        }
        try {
            $cartItem = new GoodsCart();
            $items  = $cartItem->getCartAll($shop_id, $uid);
            $result = $this->formatCartList($items, $lang, $shop_id);

            return $this->result(0, $result,'');
        } catch (\Exception $e) {
            return $this->result(19000,[],'unknown error');
        }
    }

    /**
     * 获取未登录用户购物车内的商品
     * @return array
     * @author jiangkun <jiangk@mutantbox.com>
     */
    public function actionGetCookieCart()
    {
        try {
            $lang    = Yii::$app->request->post('lang','en-us');
            $shop_id = (int) Yii::$app->request->post( 'shop_id', 0 );
            $items   = Yii::$app->request->post('items', []);

            if ($items) {
                $items = unserialize($items);
                foreach ($items as $id => $item) {
                    if(GoodsSku::findOne($item['item_id'])){
                        $items[$id] = $item;
                    }
                }
            }
            $result = $this->formatCartList($items, $lang, $shop_id);

            return $this->result(0, $result,'');

        } catch (\Exception $e) {
            return $this->result(19000,[],'unknown error');
        }
    }

    /**
     * 格式化购物车数据
     * @param $items
     * @param $lang
     * @return array
     * @author jiangkun <jiangk@mutantbox.com>
     */
    private function formatCartList($items, $lang, $shop_id)
    {
        $id = array_keys($items);
        $goods = \app\modules\shop\services\Goods::products($id, $shop_id, $lang);
        $goods = ArrayHelper::index($goods, 'goods_sku_id');
        $return = [];
        foreach ($items as $sku_id => &$item){
            if (!isset($goods[$sku_id]) || $goods[$sku_id]['good_status'] !=1 || $goods[$sku_id]['status'] != 1) {
                continue;
            }
            $item['store']    = $goods[$sku_id]['store'];
            $item['name']     = $goods[$sku_id]['name'];
            $item['cover']    = $goods[$sku_id]['cover'];
            $item['goods_id'] = $goods[$sku_id]['goods_id'];
            $item['spec']     = $goods[$sku_id]['spec'];

            //谷歌统计
            $gtm            = Goods::getGtm($item['item_id'],$item['number']);
            $item['gtm_id'] = $gtm['id'];
            $item['gtm']    = json_encode($gtm);
            $return[] = $item;
        }
        return $return;
    }


    /**
     * 获取购物车商品数量
     * @return array
     * @author jiangkun <jiangk@mutantbox.com>
     */
    public function actionGetItemNumber()
    {
        $uid     = Yii::$app->request->post('uid', 0);
        $shop_id = (int) Yii::$app->request->post( 'shop_id', 0 );

        if (!$uid) {
            return $this->result(10001,[],'Please login first!');
        }

        $number = $this->getCartNumber($uid, $shop_id);
        return $this->result(0, ['number' => $number],'');
    }

    /**
     * 获取用户购物车数量
     *
     * @param $uid
     * @param $shop_id
     * @return int
     */
    public function getCartNumber($uid, $shop_id)
    {
        $sku_ids = [];
        $items = GoodsCart::find()
            ->select(['item_id', 'number'])
            ->where(['uid' => $uid, 'shop_id' => $shop_id])
            ->indexBy('item_id')
            ->asArray()
            ->all();
        $id = array_keys($items);
        try {
            $goods = \app\modules\shop\services\Goods::products($id, $shop_id, 'en-us');
            $goods = ArrayHelper::index($goods, 'goods_sku_id');
            foreach ($items as $sku_id => $item){
                if (!isset($goods[$sku_id]) || $goods[$sku_id]['good_status'] !=1 || $goods[$sku_id]['status'] != 1) {
                    continue;
                }
                if (!in_array($sku_id, $sku_ids)) {
                    $sku_ids[] = $sku_id;
                }
            }

            $number = count($sku_ids);
            return intval($number);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * 获取商品运费策略
     * @return array
     * @author jiangkun <jiangk@mutantbox.com>
     */
    public function actionGoodsPromotion()
    {
        $goodsPromotion = GoodsPromotion::find()->where(['promotion_id' => 1])->one();
        $promotion = [];
        if (isset($goodsPromotion->json)){
            $promotion = json_decode($goodsPromotion->json, true);
        }
        return $this->result(0, $promotion ,'');
    }

    /**
     * 获取 GTM 广告
     * @return array
     * @author jiangkun <jiangk@mutantbox.com>
     */
    public function actionGtm()
    {
        $goods_sku_id = Yii::$app->request->get('goods_sku_id');
        $number       = Yii::$app->request->get('number');
        $gtm = Goods::getGtm($goods_sku_id, $number);

        return $this->result(0, ['gtm' => $gtm], '');
    }

    /**
     * 根据商品 SKU ID 获取商品
     * @return array
     * @author jiangkun <jiangk@mutantbox.com>
     */
    public function actionGoodsSku()
    {
        $goods_sku_id = Yii::$app->request->get('goods_sku_id');
        $sku = GoodsSku::find()->where(["goods_sku_id"  => $goods_sku_id])->one();
        $goods = [];
        if ($sku) {
            $goods = Goods::find()->where(['id' => $sku->goods_id])->one();
        }

        return $this->result(0, ['sku' => $sku, 'goods' => $goods], '');
    }

    /**
     * 检查商品库存
     * @return array
     * @author jiangkun <jiangk@mutantbox.com>
     */
    public function actionCheckStore()
    {
        $items = Yii::$app->request->get('items');
        $lang    = Yii::$app->request->get('lang','en-us');
        $shop_id = (int) Yii::$app->request->get( 'shop_id', 0 );
        $items = json_decode($items, true);
        $id = array_keys($items);
        $goods = \app\modules\shop\services\Goods::products($id, $shop_id, $lang);
        $goods = ArrayHelper::index($goods, 'goods_sku_id');
        $messages = [];
        foreach ($items as $sku_id => $item) {
            if (!isset($goods[$sku_id])) {
                continue;
            }
            if ($goods[$sku_id]['status'] == 0) {
                $messages[] = ['name' => $goods[$sku_id]['name'], 'tips' => 'Sold out'];
            }
            if($goods[$sku_id]['store'] <= 0 || $goods[$sku_id]['store'] < $item['number']) {
                $messages[] = ['name' => $goods[$sku_id]['name'], 'tips' => 'Out of Stock.'];
            }
        }
        if (empty($messages)) {
            return $this->result(0, [], 'success');
        }
        return $this->result(1, $messages, '');
    }
}
