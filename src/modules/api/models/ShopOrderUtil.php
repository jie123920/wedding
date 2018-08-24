<?php

namespace app\modules\api\models;
use app\modules\api\models\GoodsSpecIndex;
use yii;

class ShopOrderUtil {
    private $items;

    private $totalAmount = 0;
    private $goods_ids = [];
    private $goods_sku_ids = [];
    private $categories = [];

    public function __construct($items, $needComplete = true) {
        $this->items = $items;
        if ($needComplete) {
            $this->generateProductList();
            $this->getCategory();
        }
    }

    public static function processCart($cart) {
        $newCart = [];
        foreach ($cart as $item) {
            $newCart[$item['item_id']] = $item;
        }
        return $newCart;
    }

    public function calc() {
        $this->totalAmount = 0;
        foreach ($this->items as $itemId => $item) {
            $goods_id = $item['goods_id'];
            $amount = $item['price'] * $item['number'];
            $this->items[$itemId]['amount'] = $amount;
            $this->items[$itemId]['categories'] = $this->categories[$goods_id];
            $this->totalAmount += $amount;
        }
    }

    public function setNumber($itemId, $number) {
        $this->items[$itemId]['number'] = $number;
    }

    public function getItems() {
        return $this->items;
    }

    public function getTotal() {
        return $this->totalAmount;
    }

    public function generateProductList () {
            $tmpIds = array_keys($this->items);
            $items = GoodsSku::find()
                ->select('goods_sku.*, goods.status as goods_status, goods.name')
                ->leftJoin('goods', 'goods.id=goods_sku.goods_id')
                ->where(['in', 'goods_sku_id', $tmpIds])
                ->asArray()
                ->indexBy('goods_sku_id')
                ->all();

            foreach ($items as $itemId => $item) {
                $items[$itemId]['number'] = $this->items[$itemId]['number'];
            }
            $this->items = $items;
            $this->goods_ids = array_column($items, 'goods_id');

    }
    
    /**
     * 获取商品类别
     * 2017年5月26日 上午10:35:13
     * @author liyee
     */
    public function getCategory() {
        $goodscategoryindex = GoodsCategoryIndex::find()->select(['goods_id','category_id'])->where(['in', 'goods_id', $this->goods_ids])->andWhere(['shop_id'=>1])->asArray()->all();
        $categories = [];
        foreach ($goodscategoryindex as $item){
            $goods_id = $item['goods_id'];
            $category_id = $item['category_id'];            
            $categories[$goods_id] = [];
            
            if (!in_array($category_id, $categories[$goods_id])){
                $categories[$goods_id][] = $category_id;
            }
        }
        
        $this->categories = $categories;
    }
}
