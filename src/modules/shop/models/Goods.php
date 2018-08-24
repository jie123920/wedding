<?php

namespace app\modules\shop\models;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "goods".
 *
 * @property string $id
 * @property string $category_id
 * @property integer $people_id
 * @property string $bn
 * @property string $description
 * @property string $name
 * @property string $spec_description
 * @property string $spec_cover
 * @property string $cover
 * @property string $price
 * @property string $store
 * @property integer $status
 * @property string $sort
 * @property string $up_time
 * @property string $down_time
 * @property string $created_time
 * @property string $updated_time
 *
 * @property GoodsPeopleItem[] $goodsPeopleItems
 */
class Goods extends \yii\db\ActiveRecord {
    const PER_PAGE = 90;
    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb() {
        return Yii::$app->get('db_shop');
    }

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'goods';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['category_id', 'people_id', 'store', 'status', 'sort', 'up_time', 'down_time', 'created_time', 'updated_time'], 'integer'],
            [['description', 'spec_description'], 'string'],
            [['cover'], 'required'],
            [['price','price_original'], 'number'],
            [['bn', 'name', 'cover'], 'string', 'max' => 200],
            [['spec_cover'], 'string', 'max' => 255],
        ];
    }

    public function fields() {
        $field = [
            'id'               => 'id',
            'categoryID'       => 'category_id',
            'BN'               => 'bn',
            'spec_cover'       => 'spec_cover',
            'cover'            => 'cover',
            'price'            => 'price',
            'price_original'            => 'price_original',
            'store'            => 'store',
            'status'           => 'status',
            'sort'             => 'sort',
            'upTime'           => 'up_time',
            'downTime'         => 'down_time',
            'type'             =>'type',
            'link'             =>'link',
            'price_min'         => 'price_min',
            'price_max'         =>  'price_max',
            'discount_min'      => 'discount_min' ,
            'discount_max'      => 'discount_max'
        ];

        $langFieldArr = [
            'name'            => 'name',
            'description'     => 'description',
            'specDescription' => 'spec_description',
            'designer_description' => 'designer_description',
        ];
        foreach ($langFieldArr as $showName => $fieldName) {
            $field[$showName] = function ($model, $fieldName) {
                foreach ($model->language as $langModel) {
                    if ($langModel->table_field === $fieldName) {
                        return $langModel->content;
                    }
                }
                return null;
            };
        }

        return $field;
    }

    //根据分类得到商品
    public static function list_by_category($uid=0,$category=0,$spec=[],$designer=[],$orderBy='up_time DESC',$lang='en-us',$page=1,$shop_id=SHOP_ID,$rate=THINK_RATE_M){
        $cacheKey = CACHE_PREFIX."_".__FILE__.__FUNCTION__."_".serialize($category).'_'.$orderBy.'_'.$lang.'_'.$page.'_'.$lang."_".$shop_id."_".serialize($spec)."_".serialize($designer)."_".$rate;
        if ($return = \Yii::$app->redis->hget(CACHE_PREFIX."_goods_list_by_category_id:$category",$cacheKey)) {
            $return = unserialize($return);
            $return['data'] = self::_init_goods_hot_data($return['data'],$uid);//重新得到最新的价格和其他重要实时数据
            return $return;
        }
        $all_category = (new GoodsCategory())->get_categories_tree($category,$lang,$shop_id);
        $category_ids = self::get_category_id($all_category);
        $category_ids[] = $category;
        $category_id = $category_ids;
        $return = $where = [];
        $where['goods.status'] = 1;
        if($spec) $where['ii.spec_value_id'] = $spec;//按规格筛选
        if($designer) $where['goods.brand_id'] = $designer;//按品牌筛选
        $where['i.shop_id'] = $shop_id;


        if($spec){//有规格条件LEFT JOIN goods_spec_index
            if($orderBy == 'sell DESC'){//按销量查询
                if($category_id){
                    $where['i.category_id'] = $category_id;
                    $data = self::find()
                        ->join('RIGHT JOIN','goods_category_index as i','i.goods_id = goods.id')
                        ->join('LEFT JOIN','goods_properties as p','p.goods_id = goods.id AND p.name="1"')
                        ->orderBy('p.value DESC,goods.up_time DESC')
                        ->where($where)
                        ->limit(self::PER_PAGE)
                        ->offset(($page - 1) * self::PER_PAGE)
                        ->asArray()
                        ->all();
                    $total = self::find()
                        ->join('RIGHT JOIN','goods_category_index as i','i.goods_id = goods.id')
                        ->where($where)
                        ->count();
                }else{
                    $data = self::find()
                        ->join('LEFT JOIN','goods_properties as p','p.goods_id = goods.id AND p.name="1"')
                        ->orderBy('p.value DESC,goods.up_time DESC')
                        ->limit(self::PER_PAGE)
                        ->offset(($page - 1) * self::PER_PAGE)
                        ->asArray()
                        ->all();
                    $total = self::find()->count();
                }

            }
            else{
                if($category_id){
                    $where['i.category_id'] = $category_id;
                    $data = self::find()
                        ->distinct()
                        ->join('RIGHT JOIN','goods_category_index as i','i.goods_id = goods.id')
                        ->join('RIGHT JOIN','goods_spec_index as ii','ii.goods_id = goods.id')
                        ->where($where)
                        ->orderBy($orderBy)
                        ->limit(self::PER_PAGE)
                        ->offset(($page - 1) * self::PER_PAGE)
                        ->asArray()
                        ->all();
                    $total = self::find()
                        ->distinct()
                        ->join('RIGHT JOIN','goods_category_index as i','i.goods_id = goods.id')
                        ->join('RIGHT JOIN','goods_spec_index as ii','ii.goods_id = goods.id')
                        ->where($where)
                        ->count();
                }else{//全部商品
                    $data = self::find()
                        ->distinct()
                        ->join('RIGHT JOIN','goods_category_index as i','i.goods_id = goods.id')
                        ->join('RIGHT JOIN','goods_spec_index as ii','ii.goods_id = goods.id')
                        ->where($where)
                        ->orderBy($orderBy)
                        ->limit(self::PER_PAGE)
                        ->offset(($page - 1) * self::PER_PAGE)
                        ->asArray()
                        ->all();
                    $total = self::find()
                        ->distinct()
                        ->join('RIGHT JOIN','goods_category_index as i','i.goods_id = goods.id')
                        ->join('RIGHT JOIN','goods_spec_index as ii','ii.goods_id = goods.id')
                        ->where($where)
                        ->count();
                }

            }
        }else{
            if($orderBy == 'sell DESC'){//按销量查询
                if($category_id){
                    $where['i.category_id'] = $category_id;
                    $data = self::find()
                        ->join('RIGHT JOIN','goods_category_index as i','i.goods_id = goods.id')
                        ->join('LEFT JOIN','goods_properties as p','p.goods_id = goods.id AND p.name="1"')
                        ->orderBy('p.value DESC,goods.up_time DESC')
                        ->where($where)
                        ->limit(self::PER_PAGE)
                        ->offset(($page - 1) * self::PER_PAGE)
                        ->asArray()
                        ->all();
                    $total = self::find()
                        ->join('RIGHT JOIN','goods_category_index as i','i.goods_id = goods.id')
                        ->where($where)
                        ->count();
                }else{
                    $data = self::find()
                        ->join('RIGHT JOIN','goods_category_index as i','i.goods_id = goods.id')
                        ->join('LEFT JOIN','goods_properties as p','p.goods_id = goods.id AND p.name="1"')
                        ->orderBy('p.value DESC,goods.up_time DESC')
                        ->where($where)
                        ->limit(self::PER_PAGE)
                        ->offset(($page - 1) * self::PER_PAGE)
                        ->asArray()
                        ->all();
                    $total = self::find()
                        ->join('RIGHT JOIN','goods_category_index as i','i.goods_id = goods.id')
                        ->where($where)
                        ->count();
                }

            }
            else{
                if($category_id){
                    $where['i.category_id'] = $category_id;
                    $data = self::find()
                        ->distinct()
                        ->join('RIGHT JOIN','goods_category_index as i','i.goods_id = goods.id')
                        ->where($where)
                        ->orderBy($orderBy)
                        ->limit(self::PER_PAGE)
                        ->offset(($page - 1) * self::PER_PAGE)
                        ->asArray()
                        ->all();
                    $total = self::find()
                        ->distinct()
                        ->join('RIGHT JOIN','goods_category_index as i','i.goods_id = goods.id')
                        ->where($where)
                        ->count();
                }else{//全部商品
                    $data = self::find()
                        ->distinct()
                        ->join('RIGHT JOIN','goods_category_index as i','i.goods_id = goods.id')
                        ->where($where)
                        ->orderBy($orderBy)
                        ->limit(self::PER_PAGE)
                        ->offset(($page - 1) * self::PER_PAGE)
                        ->asArray()
                        ->all();
                    $total = self::find()
                        ->distinct()
                        ->join('RIGHT JOIN','goods_category_index as i','i.goods_id = goods.id')
                        ->where($where)
                        ->count();
                }

            }
        }
        if($data){
            $data = self::_get_spec($data,$lang,$uid);
        }
        $return = ['total_num'=>$total,'page'=>$page,'per_page'=>self::PER_PAGE,'data'=>$data];
        \Yii::$app->redis->hset(CACHE_PREFIX."_goods_list_by_category_id:$category",$cacheKey, serialize($return));
        \Yii::$app->redis->expire(CACHE_PREFIX."_goods_list_by_category_id:$category",CACHE_EXPIRE);

        return $return;
    }

    //根据搜索得到商品
    public static function list_by_search($uid=0,$keywords='',$spec=[],$designer=[],$orderBy='up_time DESC',$lang='en-us',$page=1,$shop_id=SHOP_ID,$rate=THINK_RATE_M){
        $cacheKey = CACHE_PREFIX."_".__FILE__.__FUNCTION__.'_'.$keywords.'_'.$orderBy.'_'.$lang.'_'.$page.'_'.$lang."_".$shop_id."_".serialize($spec)."_".serialize($designer)."_".$rate;
        if ($return = \Yii::$app->redis->hget(CACHE_PREFIX."_goods_list",$cacheKey)) {
            $return = unserialize($return);
            $return['data'] = self::_init_goods_hot_data($return['data'],$uid);
            return $return;
        }

        $return = [];
        $where['goods.status'] = 1;
        $where['l.table_name'] = 'goods';
        $where['l.table_field'] = ['name','description'];
        if($spec) $where['ii.spec_value_id'] = $spec;//按规格筛选
        if($designer) $where['goods.brand_id'] = $designer;//按品牌筛选
        $where['i.shop_id'] = $shop_id;

        if($spec){
            if($orderBy == 'sell DESC'){//按销量查询
                $data = self::find()
                    ->distinct()
                    ->join('RIGHT JOIN','goods_category_index as i','i.goods_id = goods.id')
                    ->join('RIGHT JOIN','goods_spec_index as ii','ii.goods_id = goods.id')
                    ->join('RIGHT JOIN','goods_language as l','l.table_id = goods.id')
                    ->join('LEFT JOIN','goods_properties as p','p.goods_id= goods.id AND p.name="1"')
                    ->where($where)
                    ->andWhere(["like","content",$keywords])
                    ->orderBy('p.value DESC')
                    ->limit(self::PER_PAGE)
                    ->offset(($page - 1) * self::PER_PAGE)
                    ->asArray()
                    ->all();
            }
            else{
                $data = self::find()
                    ->distinct()
                    ->join('RIGHT JOIN','goods_category_index as i','i.goods_id = goods.id')
                    ->join('RIGHT JOIN','goods_spec_index as ii','ii.goods_id = goods.id')
                    ->join('RIGHT JOIN','goods_language as l','l.table_id = goods.id')
                    ->where($where)
                    ->andWhere(["like","content",$keywords])
                    ->orderBy($orderBy)
                    ->limit(self::PER_PAGE)
                    ->offset(($page - 1) * self::PER_PAGE)
                    ->asArray()
                    ->all();
            }
            $total = self::find()
                ->distinct()
                ->join('RIGHT JOIN','goods_category_index as i','i.goods_id = goods.id')
                ->join('RIGHT JOIN','goods_spec_index as ii','ii.goods_id = goods.id')
                ->join('RIGHT JOIN','goods_language as l','l.table_id = goods.id')
                ->where($where)
                ->andWhere(["like","content",$keywords])
                ->count();
        }else{
            if($orderBy == 'sell DESC'){//按销量查询
                $data = self::find()
                    ->distinct()
                    ->join('RIGHT JOIN','goods_category_index as i','i.goods_id = goods.id')
                    ->join('RIGHT JOIN','goods_language as l','l.table_id = goods.id')
                    ->join('LEFT JOIN','goods_properties as p','p.goods_id= goods.id AND p.name="1"')
                    ->where($where)
                    ->andWhere(["like","content",$keywords])
                    ->orderBy('p.value DESC')
                    ->limit(self::PER_PAGE)
                    ->offset(($page - 1) * self::PER_PAGE)
                    ->asArray()
                    ->all();
            }
            else{
                $data = self::find()
                    ->distinct()
                    ->join('RIGHT JOIN','goods_category_index as i','i.goods_id = goods.id')
                    ->join('RIGHT JOIN','goods_language as l','l.table_id = goods.id')
                    ->where($where)
                    ->andWhere(["like","content",$keywords])
                    ->orderBy($orderBy)
                    ->limit(self::PER_PAGE)
                    ->offset(($page - 1) * self::PER_PAGE)
                    ->asArray()
                    ->all();
            }
            $total = self::find()
                ->distinct()
                ->join('RIGHT JOIN','goods_category_index as i','i.goods_id = goods.id')
                ->join('RIGHT JOIN','goods_language as l','l.table_id = goods.id')
                ->where($where)
                ->andWhere(["like","content",$keywords])
                ->count();
        }

        if($data){
            $data = self::_get_spec($data,$lang,$uid);
        }
        $return = ['total_num'=>$total,'page'=>$page,'per_page'=>self::PER_PAGE,'data'=>$data];
        \Yii::$app->redis->hset(CACHE_PREFIX."_goods_list",$cacheKey, serialize($return));
        \Yii::$app->redis->expire(CACHE_PREFIX."_goods_list",CACHE_EXPIRE);

        return $return;
    }
    //根据个人喜欢得到商品
    public static function list_by_favorite($uid,$orderBy='up_time DESC',$lang='en-us',$page=1,$shop_id=SHOP_ID){
 //       $cacheKey = CACHE_PREFIX."_uid:".$uid.__FILE__.__FUNCTION__.'_'.$orderBy.'_'.$page.'_'.$lang.'_'.$shop_id."_".THINK_RATE_M;
//        if ($return = \Yii::$app->redis->get($cacheKey)) {
//
//        }

        if($orderBy == 'sell DESC'){
            $data = Goods::find()
                ->join('RIGHT JOIN','goods_category_index as i','i.goods_id = goods.id')
                ->join('RIGHT JOIN','goods_favorite as f','f.goods_id = goods.id')
                ->join('LEFT JOIN','goods_properties as p','p.goods_id = goods.id AND p.name="1"')
                ->orderBy('p.value DESC,goods.up_time DESC')
                ->where(['f.uid'=>$uid,'goods.status'=>1,'i.shop_id'=>$shop_id])
                ->limit(self::PER_PAGE)
                ->offset(($page - 1) * self::PER_PAGE)
                ->asArray()
                ->all();
        }else{
            $data = Goods::find()
                ->join('RIGHT JOIN','goods_category_index as i','i.goods_id = goods.id')
                ->join('RIGHT JOIN','goods_favorite as f','f.goods_id = goods.id')
                ->where(['f.uid'=>$uid,'goods.status'=>1,'i.shop_id'=>$shop_id])
                ->orderBy($orderBy)
                ->limit(self::PER_PAGE)
                ->offset(($page - 1) * self::PER_PAGE)
                ->asArray()
                ->all();
        }

        $total =  GoodsFavorite::find()->where(['uid'=>$uid])->count();
        $return = [];
        if($data){
            $data = self::_get_spec($data,$lang,$uid);
            $return = ['total_num'=>$total,'page'=>$page,'per_page'=>self::PER_PAGE,'data'=>$data];
//            \Yii::$app->redis->set($cacheKey, serialize($return));
//            \Yii::$app->redis->expire($cacheKey,CACHE_EXPIRE);
        }
        return $return;
    }

    //根据专题得到商品
    public static function list_by_recommend($uid=0,$r_id=0,$spec=[],$designer=[],$orderBy='up_time DESC',$lang='en-us',$page=1,$shop_id=SHOP_ID,$rate=THINK_RATE_M){
        $cacheKey = CACHE_PREFIX."_".__FILE__.__FUNCTION__.'_'.$r_id.'_'.$orderBy.'_'.$lang.'_'.$page.'_'.$lang.'_'.$shop_id.'_'.serialize($spec).'_'.serialize($designer)."_".$rate;
        if ($return = \Yii::$app->redis->hget(CACHE_PREFIX."_goods_list",$cacheKey)) {
            $return = unserialize($return);
            $return['data'] = self::_init_goods_hot_data($return['data'],$uid);
            return $return;
        }

        $recommend = GoodsRecommend::findOne($r_id);
        $good_ids = json_decode($recommend->good_ids);
        $return = $where = [];
        $where['goods.status'] = 1;
        if($spec) $where['ii.spec_value_id'] = $spec;//按规格筛选
        if($designer) $where['goods.brand_id'] = $designer;//按品牌筛选
        //$where['i.shop_id'] = SHOP_ID;
        $where['goods.id'] = $good_ids;
        if($orderBy == 'sell DESC'){//按销量查询
            $data = self::find()
                ->join('LEFT JOIN','goods_properties as p','p.goods_id = goods.id AND p.name="1"')
                ->where($where)
                ->orderBy('p.value DESC,goods.up_time DESC')
                ->limit(self::PER_PAGE)
                ->offset(($page - 1) * self::PER_PAGE)
                ->asArray()
                ->all();
            $total = self::find()->where($where)->count();
        }else{
            $data = self::find()
                ->distinct()
                //->join('RIGHT JOIN','goods_category_index as i','i.goods_id = goods.id')
                ->join('RIGHT JOIN','goods_spec_index as ii','ii.goods_id = goods.id')
                ->where($where)
                ->orderBy($orderBy)
                ->limit(self::PER_PAGE)
                ->offset(($page - 1) * self::PER_PAGE)
                ->asArray()
                ->all();
            $total = self::find()
                ->distinct()
                //->join('RIGHT JOIN','goods_category_index as i','i.goods_id = goods.id')
                ->join('RIGHT JOIN','goods_spec_index as ii','ii.goods_id = goods.id')
                ->where($where)
                ->count();
        }
        if($data){
            $data = self::_get_spec($data,$lang,$uid);
            $return = ['total_num'=>$total,'page'=>$page,'per_page'=>self::PER_PAGE,'data'=>$data];
            \Yii::$app->redis->hset(CACHE_PREFIX."_goods_list",$cacheKey, serialize($return));
            \Yii::$app->redis->expire(CACHE_PREFIX."_goods_list",CACHE_EXPIRE);
        }
        return $return;
    }
    //根据GG广告关键字得到商品
    public static function list_by_keywords($uid=0,$keywords='',$spec=[],$designer=[],$orderBy='gki.id ASC',$lang='en-us',$page=1,$shop_id=SHOP_ID,$rate=THINK_RATE_M){
        $cacheKey = CACHE_PREFIX."_".__FILE__.__FUNCTION__.'_'.$keywords.'_'.$orderBy.'_'.$lang.'_'.$page.'_'.$lang.'_'.$shop_id.'_'.serialize($spec).'_'.serialize($designer)."_".$rate;
        if ($return = \Yii::$app->redis->hget(CACHE_PREFIX."_goods_list",$cacheKey)) {
            $return = unserialize($return);
            $return['data'] = self::_init_goods_hot_data($return['data'],$uid);
            return $return;
        }

        $keywordModel = GoodsKeywords::find()->where(['name'=>$keywords])->one();
        $kid = $cid = '';
        if($keywordModel){
            $kid = $keywordModel->id;
            $cid = $keywordModel->category_id;
            $cat_ids = [$keywordModel->category_id];
            $all_category = (new GoodsCategory())->get_categories_tree($cid,$lang,$shop_id);
            if($all_category)
            $cid = self::get_category_id($all_category,$cat_ids);
        }

        $return = $where = [];
        $where['goods.status'] = 1;
        $where['i.shop_id'] = $shop_id;
        if($spec) $where['ii.spec_value_id'] = $spec;//按规格筛选
        if($designer) $where['goods.brand_id'] = $designer;//按品牌筛选

        if($orderBy == 'sell DESC'){//按销量查询
            $where_ = $where;
            $where_['gki.kid'] = $kid;
            $find = self::find()
                ->distinct()
                ->join('LEFT JOIN','goods_properties as p','p.goods_id = goods.id AND p.name="1"')
                ->join('RIGHT JOIN','goods_category_index as i','i.goods_id = goods.id')
                ->join('RIGHT JOIN','goods_keywords_index as gki','gki.goods_id = goods.id')
                ->join('RIGHT JOIN','goods_spec_index as ii','ii.goods_id = goods.id')
                ->where($where_)
                ->orderBy('p.value DESC,goods.up_time DESC');

            $where['i.category_id'] = $cid;
            $union = self::find()
                ->distinct()
                ->join('LEFT JOIN','goods_properties as p','p.goods_id = goods.id AND p.name="1"')
                ->join('RIGHT JOIN','goods_category_index as i','i.goods_id = goods.id')
                ->join('RIGHT JOIN','goods_spec_index as ii','ii.goods_id = goods.id')
                ->where($where)
                ->orderBy('p.value DESC,goods.up_time DESC');
            $data = self::find()
                ->from(['tmpA' => $find->union($union)])
                ->offset(($page - 1) * self::PER_PAGE)
                ->limit(self::PER_PAGE)
                ->asArray()
                ->all();
            $total = self::find()->from(['tmpA' => $find->union($union)])->count();

        }else{
            $where_ = $where;
            $where_['gki.kid'] = $kid;

            $tj = self::find()
                ->distinct()
                ->join('RIGHT JOIN','goods_category_index as i','i.goods_id = goods.id')
                ->join('RIGHT JOIN','goods_keywords_index as gki','gki.goods_id = goods.id')
                ->join('RIGHT JOIN','goods_spec_index as ii','ii.goods_id = goods.id')
                ->where($where_)
                ->orderBy($orderBy);
            $tj_total  = $tj->count();
            $tj_data = $tj->offset(($page - 1) * self::PER_PAGE)->limit(self::PER_PAGE)->asArray()->all();
            $tj_cur_total  = $tj->offset(($page - 1) * self::PER_PAGE)->limit(self::PER_PAGE)->count();

            $tj_page_num = floor($tj_total/self::PER_PAGE)+1;


            $where['i.category_id'] = $cid;
            $where['gki.id'] = null;
            $cat_total = self::find()
                ->select("goods.id")
                ->distinct()
                ->join('RIGHT JOIN','goods_category_index as i','i.goods_id = goods.id')
                ->join('RIGHT JOIN','goods_spec_index as ii','ii.goods_id = goods.id')
                ->join('LEFT JOIN','goods_keywords_index as gki','gki.goods_id = goods.id AND gki.id=gki.id and gki.kid='.$kid)
                ->where($where)->count();

            if($tj_cur_total == self::PER_PAGE){
                $data = $tj_data;
            }else{
                $orderBy_ = '';
                if($orderBy !='gki.id ASC') $orderBy_ = $orderBy;
                $cat = self::find()
                    ->distinct()
                    ->join('RIGHT JOIN','goods_category_index as i','i.goods_id = goods.id')
                    ->join('RIGHT JOIN','goods_spec_index as ii','ii.goods_id = goods.id')
                    ->join('LEFT JOIN','goods_keywords_index as gki','gki.goods_id = goods.id AND gki.id=gki.id and gki.kid='.$kid)
                    ->where($where)->orderBy($orderBy_);
                $off = self::PER_PAGE*$tj_page_num-$tj_total;
                if($tj_cur_total > 0){
                    $per_page = self::PER_PAGE-$tj_cur_total;
                    $offset = 0;
                }else{//没有推荐商品的那一页
                    $per_page = self::PER_PAGE;
                    $offset = (($page - 1) * self::PER_PAGE)+$off-$tj_page_num*self::PER_PAGE;
                }
                $cat_data = $cat->offset($offset)->limit($per_page)->asArray()->all();
                $data = array_merge($tj_data,$cat_data);
            }
            $total = $tj_total+$cat_total;
        }
        if($data){
            $data = self::_get_spec($data,$lang,$uid);
        }
        $return = ['total_num'=>$total,'page'=>$page,'per_page'=>self::PER_PAGE,'data'=>$data];
        \Yii::$app->redis->hset(CACHE_PREFIX."_goods_list",$cacheKey, serialize($return));
        \Yii::$app->redis->expire(CACHE_PREFIX."_goods_list",CACHE_EXPIRE);
        return $return;
    }

    //得到商品规格等详细信息
    public static function _get_spec($data,$lang,$uid){
        foreach ($data as &$good){
            $model = Goods::findOne($good['id']);
            $specArr = $specValArr = $productArr = $skuArr = $specValIDArr = [];
            $store = 0;
            foreach ($model->getSku()->with('specValue')->all() as $skuModel) {
                $store +=$skuModel->store;
                foreach ($skuModel->specValue as $specValueModel) {
                    if($specValueModel->category->spec_type != 'image'){
                        $specValueModel->spec_image = '';
                    }else{
                        $photos = GoodsPhoto::find()->where(['goods_id'=>$model->id,'spec_value_id'=>$specValueModel->spec_value_id])->orderBy('place ASC')->one();
                        $specValueModel->spec_image = $photos?$photos->url:$specValueModel->spec_image;
                    }
                    $specValArr[$specValueModel->spec_id][$specValueModel->spec_value_id] = $specValueModel;
                    $specArr[$specValueModel->spec_id]                                    = $specValueModel->spec_id;
                    $specValIDArr[$specValueModel->spec_value_id] = $specValueModel->spec_value_id;
                }
                $tmpSpecVal = ArrayHelper::getColumn($skuModel->specValue, 'spec_value_id');
                sort($tmpSpecVal);
                $productArr[implode(';', $tmpSpecVal)] = (object) [
                    'price' => $skuModel->price,
                    'count' => $skuModel->store,
                    'id'    => $skuModel->goods_sku_id,
                ];
                $skuArr[$skuModel->goods_sku_id] = $skuModel;
            }


            $specValIDArr = GoodsSpecValue::find()->where(['spec_value_id' => $specValIDArr])->orderBy('sort desc')->all();
            foreach ($specValArr as &$o){//规格值进行排序
                $new_option_ = [];
                foreach ($specValIDArr as $s){
                    if(!isset($o[$s->spec_value_id])) continue;
                    $new_option_[] = $o[$s->spec_value_id];
                }
                $o = $new_option_;
            }
            $new_option = [];
            $specArr = GoodsSpec::find()->where(['spec_id' => $specArr])->orderBy('sort desc')->all();
            foreach ($specArr as $s){//规格分类进行排序
                if(!isset($specValArr[$s->spec_id])) continue;
                $new_option[] = $specValArr[$s->spec_id];
            }
            $brand_name = '';
            if($model->brand){
                $brand = $model->brand;
                $brand_name = $brand->brand_name;
            }
            $category_info = '';
            if($model->categories){
                $category = $model->categories[0];
                $category_info = GoodsCategory::findOne($category->category_id);
            }
            $good['category_name'] = $category_info?$category_info->name:'';
            $good['spec'] = $new_option;//spec
            $good['spec_values'] = $productArr;//spec values
            $good['BN'] =  json_encode((object)$productArr);
            $good['is_empty'] = $store>0 ? 1 : 0;//代表是否库存为空：1不为空0为空
            $good['brand_name'] = $brand_name;
            //多语言
            $rec_language = $model->getLanguage($lang)->all();
            if(!empty($rec_language)) {
                $mapArr = ArrayHelper::map($rec_language, 'table_field','content');
                $good['name'] = isset($mapArr['name'])?$mapArr['name']:$good['name'];
                $good['description'] = isset($mapArr['description'])?$mapArr['description']:$good['description'];
            }
        }

        $data = self::_init_goods_favorite($data,$uid);

        return $data;
    }


    /**
     * 获取收藏的商品id列表
     * @param int $uid
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function _get_favorite($uid=0){

        $cacheKey = CACHE_PREFIX."_get_favorite_uid:".$uid;
        if ($data = \Yii::$app->redis->get($cacheKey)) {
            //return unserialize($data);
        }

        $data = Goods::find()
            ->join('RIGHT JOIN','goods_favorite as f','f.goods_id = goods.id')
            ->where(['f.uid'=>$uid])
            ->all();
        if($data){
            $data = ArrayHelper::getColumn($data,'id');
        }

        \Yii::$app->redis->set($cacheKey, serialize($data));
        \Yii::$app->redis->expire($cacheKey,CACHE_EXPIRE);

        return $data;
    }

    //遍历商品个人喜欢的属性
    public static function _init_goods_favorite($goods,$uid){
        $favorite = self::_get_favorite($uid);
        foreach ($goods as &$model){
            if(in_array($model['id'],$favorite)){
                $model['if_favorite'] = 1;//if favorite
            }else{
                $model['if_favorite'] = 0;
            }
        }
        return $goods;
    }


    /**
     * 重要的数据需要另外读取 要保持和数据库一致性 不能有延迟
     * @param $goods
     * @return mixed
     */
    public static function _init_goods_hot_data($goods,$uid){
        $goods_ids = ArrayHelper::getColumn($goods,'id');
        $data = self::GetGoodsById($goods_ids);
        $goods = self::_init_goods_favorite($goods,$uid);
        foreach ($goods as &$model){
            $hot_data = isset($data[self::getKey($model['id'])]) ? $data[self::getKey($model['id'])] :[];
            if($hot_data){
                $model['price'] = $hot_data['price'];
                $model['price_original'] = $hot_data['price_original'];
                $model['price_min'] = $hot_data['price_min'];
                $model['price_max'] = $hot_data['price_max'];
                $model['discount_min'] = $hot_data['discount_min'];
                $model['discount_max'] = $hot_data['discount_max'];
            }
        }
        return $goods;
    }

    /**
     * 批量获取商品重要字段 没有则读取数据库 然后缓存
     * @param array $goods_ids
     * @return array
     */
    public static function  GetGoodsById($goods_ids = []){
        $data = [];
        // 从缓存获取数据
        $cacheKeys = $dbResult =$cacheResult = [];
        foreach ($goods_ids as $id) {
            $cacheKeys[] = self::getKey( $id );
        }

        try {
            $cacheResult = Yii::$app->cache->mget( $cacheKeys );
        } catch (\Exception $e ) {
            Yii::error( $e->getFile() . ':' . $e->getLine() . ':' . $e->getMessage() );
        }

        $cached_ids = ArrayHelper::getColumn($cacheResult,'id');
        //没缓存的 读取数据 生成缓存
        $missIds = array_diff( $goods_ids,$cached_ids);

        if ( $missIds ) {
            $dbResult = self::find()
                ->select('
                        id,
                        price,
                        price_original,
                        price_min,
                        price_max,
                        discount_min,
                        discount_max
                ')
                ->where(['id'=>$missIds])
                ->asArray()
                ->all();
            foreach ($dbResult as $value){
                $data[self::getKey( $value['id'] )] = $value;
            }

            try {
                Yii::$app->cache->mset($data,CACHE_EXPIRE);
            } catch (\Exception $e ) {
                YII::error( $e->getFile() . ':' . $e->getLine() . ':' . $e->getMessage() );
            }
        }

        $data = array_merge($cacheResult,$dbResult);

        return $data;
    }

    public static function get_category_id($all_category,&$cat_ids = []){
        foreach ($all_category as $c){
            $cat_ids[] = intval($c['id']);
            if($c['cat_id']){
                $cat_ids =  array_merge($cat_ids,self::get_category_id($c['cat_id']));
            }
        }
        return $cat_ids;
    }

    public static function getKey( $id ) {
        return sprintf( CACHE_PREFIX.'_good_info_id=%d', $id );
    }

    //获取单个商品的详细信息
    public static function One($id,$sku_id,$shop_id=SHOP_ID,$lang=LANG_SET,$rate=THINK_RATE_M){
        $cacheKey = CACHE_PREFIX."_".__FILE__.__FUNCTION__.$shop_id.$rate.$lang.'_'.$id.'_'.$sku_id;
        if ($return = \Yii::$app->redis->hget(CACHE_PREFIX."_good_one_".$id,$cacheKey)) {
            return unserialize($return);
        }

        $model = self::findOne($id);
        if(!$model){
            $sku = GoodsSku::find()->where(['goods_sku_id'=>$sku_id])->one();
            $model = self::findOne($sku->goods_id);
        }
        if(!$model) return [];

        $good = $model->toArray();

        //多语言
        $langArr = ArrayHelper::map($model->language, 'table_field', 'content', 'language');
        $good['name'] = isset($langArr[$lang]['name']) ? $langArr[$lang]['name'] : ( isset($langArr['en-us']['name']) ? $langArr['en-us']['name'] : '');
        $good['description'] = isset($langArr[$lang]['description']) ? $langArr[$lang]['description'] : ( isset($langArr['en-us']['description']) ? $langArr['en-us']['description'] : '');
        $good['designer_description'] = isset($langArr[$lang]['designer_description']) ? $langArr[$lang]['designer_description'] : ( isset($langArr['en-us']['designer_description']) ? $langArr['en-us']['designer_description'] : '');
        $good['spec_description'] = isset($langArr[$lang]['spec_description']) ? $langArr[$lang]['spec_description'] : ( isset($langArr['en-us']['spec_description']) ? $langArr['en-us']['spec_description'] : '');

        //brand name
        $good_brand_name =  '';
        if($model->brand){
            $brand = $model->brand;
            $good_brand_name = $brand->brand_name;
        }
        $good['good_brand_name'] = $good_brand_name;
        $all = $model->getSku()->with('specValue')->all();
        $specArr = $specValArr = $productArr = $skuArr = $specValIDArr = $tmpSpecVal = [];
        foreach ($all as $skuModel) {
            $tmpSpecVal = [];
            foreach ($skuModel->specValue as $specValueModel) {
                $tmpSpecVal[] = $specValueModel->spec_value_id;
                if($specValueModel->category->spec_type != 'image'){
                    $specValueModel->spec_image = '';
                }else{
                    $photos = GoodsPhoto::find()->where(['goods_id'=>$model->id,'spec_value_id'=>$specValueModel->spec_value_id])->orderBy('place ASC')->one();
                    $specValueModel->spec_image = $photos?$photos->url:$specValueModel->spec_image;
                }
                $specValArr[$specValueModel->spec_id][$specValueModel->spec_value_id] = $specValueModel;
                $specArr[$specValueModel->spec_id]                                    = $specValueModel->spec_id;
                $specValIDArr[$specValueModel->spec_value_id] = $specValueModel->spec_value_id;
            }
            sort($tmpSpecVal);
            $photos = GoodsPhoto::find()
                ->select('url')
                ->where(['spec_value_id'=>$tmpSpecVal,'goods_id'=>$model->id,'type'=>3])
                ->asArray()
                ->all();

            $photos  = ArrayHelper::getColumn($photos,'url');


            $productArr[implode(';', array_unique($tmpSpecVal))] = (object) [
                'price'                 => $skuModel->price,
                'price_local'           => number_format($skuModel->price*$rate, 2, '.', ''),
                'price_original'        => $skuModel->price_original,
                'price_original_local'  => number_format($skuModel->price_original*$rate, 2, '.', ''),
                'count'                 => $skuModel->store,
                'photos'                => $photos,
                'id'                    => $skuModel->goods_sku_id,
                'sorted_spec_value'     =>$tmpSpecVal
            ];


            //sku拥有的规格值ID排序，比如颜色在前 尺码在后 临时满足手机端的数据格式<<<  TODO
            $tmpSpecVals = ArrayHelper::getColumn($skuModel->specValue, 'spec_value_id');
            $spec_val_object = null;
            $spec_val_object = GoodsSpecValue::find()
                ->where(['spec_value_id'=>array_unique($tmpSpecVals)])
                ->all();
            foreach ($spec_val_object as $val_obj){
                $tmpSpecVal[$val_obj->category->sort] = $val_obj->spec_value_id;
            }
            krsort($tmpSpecVal);
            //>>>


            $skuArr[$skuModel->goods_sku_id] = $skuModel;
        }

        $specValIDArr = GoodsSpecValue::find()->where(['spec_value_id' => $specValIDArr])->orderBy('sort desc')->all();
        //规格值进行排序
        foreach ($specValArr as &$o){
            $new_option_ = [];
            foreach ($specValIDArr as $s){
                if(!isset($o[$s->spec_value_id])) continue;
                $new_option_[] = $o[$s->spec_value_id];
            }
            $o = $new_option_;
        }

        //获取第一个规格作为默认选中的规格
        foreach ($productArr as $k=> $pro){
            $sku_id = $sku_id?$sku_id:$pro->id;
            break;
        }
        //如果有指定sku ID则读库
        $default_sku_info = GoodsSku::findOne($sku_id);

        //获取当前SKU的规格ids
        $default_sku_spec_value_ids = '';
        foreach ($productArr as $k=>$pro){
            if($pro->id == $sku_id && $pro->id>0){
                $default_sku_spec_value_ids  = $k;
            }
        }


        //规格分类
        $specArr = GoodsSpec::find()->where(['spec_id' => $specArr])->orderBy('sort desc')->indexBy('spec_id')->all();
        //面包屑

        $c = GoodsCategoryIndex::find()->where(['goods_id'=>$model->id,'shop_id'=>$shop_id])->one();
        if(!$c){
           return [];
        }
        $bread = (new GoodsCategory())->getBread($c->category_id);
        //wear it with
        $related_goods = $related_goods_tmp  = $mapArr = [];
        if($model->relation){
            $related_goods_tmp = self::find()
                ->where(['id'=>json_decode($model->relation,true)])
                ->all();//对象
            foreach ($related_goods_tmp as $k=>&$rec){

                $brand = '';
                if($rec->brand){
                    $brand = $rec->brand;
                    $brand = $brand->brand_name;
                }

                $rec_language = $rec->getLanguage()->all();
                if(!empty($rec_language)) {
                    $mapArr = ArrayHelper::map($rec_language, 'table_field','content');
                    $rec->name = isset($mapArr['name'])?$mapArr['name']:$rec->name;
                    $rec->description = isset($mapArr['description'])?$mapArr['description']:$rec->description;
                    $rec->spec_description = isset($mapArr['spec_description'])?$mapArr['spec_description']:$rec->spec_description;
                }

                $_related_goods = $rec->toArray();
                $_related_goods['brand_name'] = $brand;
                $related_goods[] = $_related_goods;
            }
        }

        //推荐商品
        $recommend = $recommend_tmp  = $mapArr = [];
        if($model->recommend){
            $recommend_tmp = self::find()
                ->where(['id'=>json_decode($model->recommend,true)])
                ->all();//对象

            foreach ($recommend_tmp as $k=>&$rec){

                $brand = '';
                if($rec->brand){
                    $brand = $rec->brand;
                    $brand = $brand->brand_name;
                }


                $rec_language = $rec->getLanguage()->all();
                if(!empty($rec_language)) {
                    $mapArr = ArrayHelper::map($rec_language, 'table_field','content');
                    $rec->name = isset($mapArr['name'])?$mapArr['name']:$rec->name;
                    $rec->description = isset($mapArr['description'])?$mapArr['description']:$rec->description;
                    $rec->spec_description = isset($mapArr['spec_description'])?$mapArr['spec_description']:$rec->spec_description;
                }


                $_recommend = $rec->toArray();
                $_recommend['brand_name'] = $brand;
                $recommend[] = $_recommend;
            }
        }

        $c = GoodsComment::Get($good['id'],1,5,$shop_id);
        $comment = $c['comment'];
        $comment_count = $c['total_count'];


        //GTM<<<<<<<<<<
        $end = end($bread);
        $category_name = $end['name'];
        $varient = $goods_sku_id= $price = '';
        if($default_sku_info){
            foreach($default_sku_info->specValue as $v){
                $varient .= $v->spec_value.",";
            }
            $goods_sku_id = $default_sku_info->goods_sku_id;
            $price =  $default_sku_info->price;
        }

        $varient = rtrim($varient,",");
        $GTM = [];
        $GTM['name'] = $good['name'];
        $GTM['id'] = $model->bn."_".$goods_sku_id;
        $GTM['price'] = $price;
        $GTM['brand'] = $good['good_brand_name'];
        $GTM['category'] = $category_name;
        $GTM['variant'] = $varient;
        //GTM>>>>>>


        //相册
        $goods_photo = [];
        if(!empty($model->photo)){
            foreach ($model->photo as $p){
                $goods_photo[] = $p->url;
            }
        }
        $good['goods_photo'] = $goods_photo;


       // echo '<pre>';print_r($comment);exit;

        $data = [
            'good'            =>$good,
            'bread'           =>$bread,
            'related_goods'   =>$related_goods,
            'recommend'       =>$recommend,
            'specArr'         =>$specArr,
            'default_sku_info'=>$default_sku_info,
            'spec_value_ids'  =>$default_sku_spec_value_ids,
            'gtm'             => json_encode(array($GTM)),
            'specValArr'      => $specValArr,
            'productObj'      => (object) $productArr,
            'skuArr'          => $skuArr,
            'comment'        => $comment,
            'comment_count'  =>$comment_count
        ];

        \Yii::$app->redis->hset(CACHE_PREFIX."_good_one_".$id,$cacheKey, serialize($data));
        \Yii::$app->redis->expire(CACHE_PREFIX."_good_one_".$id,CACHE_EXPIRE);

        return $data;

    }



    //谷歌分析
    public static function getGtm($goods_sku_id,$quantity){
        //谷歌分析<<<
        $sku_info = GoodsSku::findOne($goods_sku_id);
        $varient = '';
        foreach($sku_info->specValue as $v){
            $varient .= $v->spec_value.",";
        }
        $good = Goods::findOne($sku_info->goods_id);
        $brand = $good->brand;
        $category_info = '';

        $c = GoodsCategoryIndex::find()->where(['goods_id'=>$good->id,'shop_id'=>SHOP_ID])->one();
        if($c){
            $category_info = GoodsCategory::findOne($c->category_id);
        }
        $varient = rtrim($varient,",");

        //language
        $langArr = ArrayHelper::map($good->language, 'table_field', 'content', 'language');
        //good name
        $name = isset($langArr[LANG_SET]['name']) ? $langArr[LANG_SET]['name'] : ( isset($langArr['en-us']['name']) ? $langArr['en-us']['name'] : '');
        $name = $name ?$name: ( isset($langArr['en-us']['name']) ? $langArr['en-us']['name'] : '');

        $gtm['name'] = $name;
        $gtm['id'] = $good->bn."_".$sku_info->goods_sku_id;
        $gtm['price'] = $sku_info->price;
        $gtm['brand'] = $brand?$brand->brand_name:'';
        $gtm['category'] = $category_info?str_replace("'",' ',$category_info->name):'';
        $gtm['variant'] = $varient;
        $gtm['quantity'] = $quantity;
        //谷歌分析>>>

        return $gtm;
    }



    public function getGoodsPeopleItems() {
        return $this->hasMany(GoodsPeopleItem::className(), ['item_id' => 'id']);
    }

    public function getSku() {
        return $this->hasMany(GoodsSku::className(), ['goods_id' => 'id'])
            ->where(['status' => '1']);
    }

    public function getLanguage() {
        return $this->hasMany(GoodsLanguage::className(), ['table_id' => 'id'])
            ->where(['table_field' => ['name', 'description', 'spec_description', 'designer_description'], 'table_name' => 'goods']);
    }

    public function getPhoto() {
        return $this->hasMany(GoodsPhoto::className(), ['goods_id' => 'id'])->orderBy(['place' => SORT_ASC]);
    }

    public function getCategory() {
        return $this->hasOne(GoodsCategory::className(), ['id' => 'category_id']);
    }

    public function getProperties() {
        return $this->hasOne(GoodsProperties::className(), ['goods_id' => 'id']);
    }

    public function getBrand() {
        return $this->hasOne(GoodsBrand::className(), ['brand_id' => 'brand_id']);
    }

    public function getCategories() {
        return $this->hasMany(GoodsCategoryIndex::className(), ['goods_id' => 'id'])->where(['shop_id'=>SHOP_ID]);
    }
}
