<?php

namespace app\modules\shop\models;
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

    public function get_categories_tree($cat_id = 0,$lang=LANG_SET,$shop_id=2) {

        $cacheKey = CACHE_PREFIX."_".__FILE__.__FUNCTION__."_".$cat_id."_".$lang."_".$shop_id;
        if ($data = \Yii::$app->redis->get($cacheKey)) {
            return unserialize($data);
        }

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
            $data = array_values($cat_arr);
            \Yii::$app->redis->set($cacheKey, serialize($data));
            \Yii::$app->redis->expire($cacheKey,CACHE_EXPIRE);
            return $data;
        }
    }

    /**
     * 获取面包屑
     * @param int $category_id
     */
    public  function getBread($category_id = 0){

        $cacheKey = CACHE_PREFIX."_".__FILE__.__FUNCTION__."_".$category_id;
        if ($data = \Yii::$app->redis->get($cacheKey)) {
            return unserialize($data);
        }

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

        if($arr){
            \Yii::$app->redis->set($cacheKey, serialize($arr));
            \Yii::$app->redis->expire($cacheKey,CACHE_EXPIRE);
        }
        return $arr;
    }



    public function getBreadStr($category_id = 0){
        $arr = $this->getBread($category_id);
        $bread = '';
        foreach ($arr as $i=>$b){
            $bread .= "<a href='/goods/list?category_id=".$b['id']."'>".$b['name']."</a>".(isset($arr[$i+1])?"->":"");
        }
        return $bread;
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


    public function getChild($parent_id){
        $cacheKey = CACHE_PREFIX."_".__FILE__.__FUNCTION__."_".$parent_id;
        if ($return = \Yii::$app->redis->get($cacheKey)) {
            //return unserialize($return);
        }


        $arr = [];
        $cat_list = self::find()
            ->select('id')
            ->where(['parent_id'=>$parent_id])
            ->from(self::tableName())
            ->asArray()
            ->all();
        if($cat_list){
            $cat_list = yii\helpers\ArrayHelper::getColumn($cat_list,'id');
            $arr = array_merge($cat_list,$this->getChild($cat_list));
        }

        \Yii::$app->redis->set($cacheKey, serialize($arr));
        \Yii::$app->redis->expire($cacheKey,CACHE_EXPIRE);

        return $arr;
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

    /**
     * 获取 SEO 信息
     * @param string $lang
     * @return $this
     */
    public function getSeoInfo($lang = LANG_SET)
    {
        return $this->hasMany(GoodsLanguage::className(), ['table_id' => 'id'])
            ->where(['table_field'=> ['title', 'description', 'keyword'],'table_name'=>'goods_category','language'=>$lang]);
    }

    public static function is_parent_category($category_id)
    {
        $model = self::One($category_id);
        if($model && $model->parent_id == 0) return true;
        return false;
    }

    public static function One($id,$lang){
        $cacheKey = CACHE_PREFIX."_".__FILE__.__FUNCTION__."_".$id."_".$lang;
        if ($return = \Yii::$app->redis->get($cacheKey)) {
            return unserialize($return);
        }

        $data = self::findOne($id);
        if(!$data) return [];

        $data_arr = $data->toArray();

        $language = $data->getLanguage($lang)->all();

        if ($language) {
            foreach ($language as $row) {
                if ($row['language'] !== $lang) {
                    continue;
                }
                switch ($row['table_field']) {
                    case 'title':
                        $data_arr['meta_title'] = $row['content'];
                        break;
                    case 'description':
                        $data_arr['description'] = $row['content'];
                        break;
                    case 'keyword':
                        $data_arr['keyword'] = $row['content'];
                        break;
                    case 'name':
                        $data_arr['cate_name'] =  $row['content'];
                        break;
                    default :
                        break;
                }
            }
        }

        $all = [];
        $parent_id = $data->parent_id;
        while ($parent_id!=0){
            $GoodsCategory_parent = self::findOne($parent_id,$lang);
            if($GoodsCategory_parent){
                $parent_id = $GoodsCategory_parent->parent_id;
                $all[] = $GoodsCategory_parent;
            }else{
                $parent_id = $data->parent_id;
            }
        }
        $all[] = $data;
        $data_arr['assoc_category_ids'] = yii\helpers\ArrayHelper::getColumn($all,'id');

        \Yii::$app->redis->set($cacheKey, serialize($data_arr));
        \Yii::$app->redis->expire($cacheKey,CACHE_EXPIRE);

        return $data_arr;
    }


    /**
     * 获取一级分类
     * @param $category_id
     */
    public static function GetLever1Category($category_id){
        while (!self::is_parent_category($category_id)){
            $One = self::One($category_id);
            $category_id = $One->parent_id;
        }
        return $category_id;
    }

}
