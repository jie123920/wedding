<?php

namespace app\modules\shop\models;
use yii;

class GoodsSku extends \yii\db\ActiveRecord {
    public static function tableName() {
        return '{{goods_sku}}';
    }

    public static function getDb() {
        return Yii::$app->get('db_shop');
    }

    public function scenarios() {
        return [
            'create'  => ['goods_sku_id', 'goods_id', 'price', 'store', 'status', 'sort', 'up_time', 'down_time', 'created_time', 'updated_time'],
            'update'  => ['goods_sku_id', 'goods_id', 'price', 'store', 'status', 'sort', 'up_time', 'down_time', 'created_time', 'updated_time'],
            'default' => ['goods_sku_id', 'goods_id', 'price', 'store', 'status', 'sort', 'up_time', 'down_time', 'created_time', 'updated_time'],
        ];
    }

    public function getSpec() {
        return $this->hasMany(GoodsSpecIndex::className(), ['goods_sku_id' => 'goods_sku_id']);
    }

    public static function getSpecInfo($items, $lang = 'en-Us') {
        $skuIds = array_keys($items);

        $specs = GoodsSpecIndex::find()
            ->select('
                goods_spec_index.*,
                goods.name as goods_name, goods.cover as goods_cover,
                goods_spec_values.spec_value_id as spec_value_id, goods_spec_values.spec_value as spec_value, goods_spec_values.spec_image as spec_image,
                goods_spec.spec_name as spec_name, goods_spec.spec_id as spec_id
            ')
            ->leftJoin('goods', '`goods`.`id` = `goods_spec_index`.`goods_id`')
            ->leftJoin('goods_spec_values', '`goods_spec_values`.`spec_value_id` = `goods_spec_index`.`spec_value_id`')
            ->leftJoin('goods_spec', '`goods_spec_values`.`spec_id` = `goods_spec`.`spec_id`')
            ->where(['in', 'goods_spec_index.goods_sku_id', $skuIds])
            ->asArray()
            ->all();

        $goodsIds = $specIds = $specValueIds = [];
        foreach ($specs as $key => $value) {
            if (!empty($value['goods_id'])) {
                $goodsIds[$value['goods_id']] = 1;
            }
            if (!empty($value['spec_id'])) {
                $specIds[$value['spec_id']]   = 1;
            }
            if (!empty($value['spec_value_id'])) {
                $specValueIds[$value['spec_value_id']] = 1;
            }
        }
        $goodsIds = array_keys($goodsIds);
        $specIds  = array_keys($specIds);
        $specValueIds = array_keys($specValueIds);

        $conditions = [];
        if (!empty($goodsIds)) {
            $conditions[] = sprintf("(table_name='goods' and table_field='name' and table_id in (%s))", implode(',', $goodsIds));
        }
        if (!empty($specIds)) {
            $conditions[] = sprintf("(table_name='goods_spec' and table_field='spec_name' and table_id in (%s))", implode(',', $specIds));
        }
        if (!empty($specValueIds)) {
            $conditions[] = sprintf("(table_name='goods_spec_values' and table_field='spec_value' and table_id in (%s))", implode(',', $specValueIds));
        }

        if (!empty($conditions)) {
            $texts = GoodsLanguage::find()
                ->where(['language' => $lang])
                ->andWhere(implode(' or ', $conditions))
                ->asArray()
                ->all();
        } else {
            $texts = [];
        }

        $goodsNames = $specNames = $specValueNames = [];
        foreach ($texts as $key => $value) {
            if($value['table_name'] == 'goods') {
                $goodsNames[$value['table_id']] = $value['content'];
            }
            if($value['table_name'] == 'goods_spec') {
                $specNames[$value['table_id']] = $value['content'];
            }
            if($value['table_name'] == 'goods_spec_values') {
                $specValueNames[$value['table_id']] = $value['content'];
            }
        }

        foreach ($specs as $value) {
            $item_id = $value['goods_sku_id'];
            if(!in_array($item_id,array_keys($items))) continue;
            if (isset($goodsNames[$value['goods_id']])) {
                $value['goods_name'] = $goodsNames[$value['goods_id']];
            }
            $items[$item_id]['name'] = $value['goods_name'];
            $photos = GoodsPhoto::find()
                ->where(['spec_value_id'=>$value['spec_value_id'],'goods_id'=>$value['goods_id'],'type'=>3])
                ->orderBy('place asc')
                ->asArray()
                ->one();
            if($photos && !isset($items[$item_id]['cover'])){
                $items[$item_id]['cover'] = $photos['url'];
            }
            if(!isset($items[$item_id]['cover'])){
                $items[$item_id]['cover'] = $value['goods_cover'];
            }
            $items[$item_id]['goods_id'] = $value['goods_id'];

            if (!isset($items[$item_id]['spec'])) {
                $items[$item_id]['spec'] = [];
            }

            if (isset($specNames[$value['spec_id']])) {
                $value['spec_name'] = $specNames[$value['spec_id']];
            }

            if (isset($specValueNames[$value['spec_value_id']])) {
                $value['spec_value'] = $specValueNames[$value['spec_value_id']];
            }
            $items[$item_id]['spec'][] = [
                'spec_name' => $value['spec_name'],
                'spec_value' => $value['spec_value'],
            ];
        }

        return $items;
    }

    public function getSpecValue() {
        return $this->hasMany(GoodsSpecValue::className(), ['spec_value_id' => 'spec_value_id'])->orderBy(['sort' => SORT_DESC])->via('spec');
    }


    public function getPhotos() {
        return $this->hasMany(GoodsPhoto::className(), ['sku_id' => 'goods_sku_id'])->orderBy(['place' => SORT_ASC]);
    }
}
