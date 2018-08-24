<?php

namespace app\modules\api\models;
use yii;
class GoodsCategory extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{goods_category}}';
    }

    public static function getDb() {
        return Yii::$app->get('db_shop');
    }

    public function get_categories_tree($cat_id = 0,$lang=LANG_SET,$shop_id=1) {
        $where[self::tableName().'.shop_id'] = $shop_id;
        $where[self::tableName().'.parent_id'] = $cat_id;
        $cat_arr = array();
        $count = self::find()
            ->from(self::tableName())
            ->where($where)
            ->count();
        if ($count) {
            $cat_res = self::find()
                ->select('goods_category.*,goods_language.content')
                ->from(self::tableName())
                ->join('LEFT JOIN','goods_language','goods_language'.'.table_id=goods_category.id AND '.'goods_language'.'.table_name=\'goods_category\' AND '.'goods_language'.'.language=\''.$lang.'\'')
                ->where($where)
                ->orderBy("sort ASC,id ASC")
                ->asArray()
                ->all();
            foreach ($cat_res AS $value) {
                $cat_arr[$value['id']]['id'] = $value['id'];
                $cat_arr[$value['id']]['name'] = $value['content']?$value['content']:$value['name'];
                $cat_arr[$value['id']]['sort'] = $value['sort'];
                $cat_arr[$value['id']]['parent_id'] = $value['parent_id'];
                if (isset($value['id']) != NULL) {
                    $cat_arr[$value['id']]['cat_id'] = $this->get_categories_tree($value['id'],$lang,$shop_id);
                }
            }
        }
        if (isset($cat_arr)) {
            return array_values($cat_arr);
        }
    }

    /**
     * 获取面包屑
     * @param int $category_id
     */
    public  function getBread($category_id = 0){
        $arr = $cat_list = array();
        $cat_list_o = self::find()
            ->select('*')
            ->from(self::tableName())
            ->all();
        $list = [];
        foreach ($cat_list_o as $i=>$o){
            $list[$i]['id'] = $o->id;
            $list[$i]['parent_id'] = $o->parent_id;
            $list[$i]['name'] = $o->name;
            if($o->language){
                $list[$i]['name'] = $o->language[0]->content?$o->language[0]->content:$list[$i]['name'];
            }
        }

        if($list){
            $arr = $this->_getParents($list,$category_id);
        }
        return $arr;
    }

    private function _getParents($cat_list,$category_id){
        $arr = [];
        foreach ($cat_list as $v) {
            if ($v['id'] == $category_id) {
                $arr[] = $v;
                $arr = array_merge($this->_getParents($cat_list, $v['parent_id']),$arr);
            }
        }
        return $arr;
    }

    public static function is_parent_category($category_id)
    {
        $model = self::findOne(['id'=>$category_id]);
        if($model && $model->parent_id == 0) return true;
        return false;
    }


    public function findModel($id)
    {
        $model = self::findOne($id);

        if ($model) {
            return $model;
        } else {
            throw new NotFoundHttpException(\Yii::t('tip', 'data not existed'));
        }
    }

    public function getType()
    {
        return $this->hasOne(GoodsType::className(), ['goods_type_id' => 'goods_type_id']);
    }

    public function getGoods()
    {
        return $this->hasMany(Goods::className(), ['category_id' => 'id']);
    }

    public function getLanguage($lang = LANG_SET)
    {
        return $this->hasMany(GoodsLanguage::className(), ['table_id' => 'id'])
            ->where(['table_field'=>'name','table_name'=>'goods_category','language'=>$lang]);
    }
}
