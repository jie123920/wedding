<?php

namespace app\modules\api\models;
use yii\data\ActiveDataProvider;
use yii;
class GoodsSpec extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{goods_spec}}';
    }

    public static function getDb() {
        return Yii::$app->get('db_shop');
    }

    public function scenarios() {
        return [
            'create' => ['spec_id','spec_name','spec_type','sort'],
            'update' => ['spec_id','spec_name','spec_type','sort'],
            'default' => ['spec_id','spec_name','spec_type','sort'],
        ];
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['spec_name'], 'required',  'message'=>'名称必填'],
            [['spec_type'], 'required',  'message'=>'类型必填'],
            [['spec_id', 'sort'], 'integer'],
            [['sort'], 'integer', 'min'=>0, 'max'=>99],
            [['spec_name'], 'safe'],
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = self::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

//        if (!$this->validate()) {
//            return $dataProvider;
//        }

        $query->andFilterWhere([
            'spec_id' => $this->spec_id,
        ]);

        $query->andFilterWhere(['like', 'spec_name', $this->spec_name]);

        $query->orderBy('spec_id desc');

        return $dataProvider;
    }


    public static function get_size_color($lang = LANG_SET){
        $cacheKey = 'shop_get_size_color';
        if ($return = \Yii::$app->cache->get($cacheKey)) {
            //return $return;
        }
        $spec = GoodsSpec::find()
            ->where(['type'=>[1,2]])
            ->orderBy("sort desc")
            ->all();
        $size = $color = [];
        foreach ($spec as $i=>$s){
            if($s->getLanguage($lang)->all()){
                $L = $s->getLanguage($lang)->all();
                $s->spec_name = $L[0]->content;
            }
            $spec_values = $s->value;
            $values = [];
            foreach ($spec_values as $j=>$value){
                if($value->getLanguage($lang)->all()){
                    $LL = $value->getLanguage($lang)->all();
                    $value->spec_value = $LL[0]->content;
                    $values[$j]['spec_image'] = $value->spec_image;
                }
                $values[$j]['spec_value_id'] = $value->spec_value_id;
                $values[$j]['spec_value'] = $value->spec_value;
            }
            if($s->type == 1 && $s->value){
                $size[$i]['spec_id'] = $s->spec_id;
                $size[$i]['spec_name'] = $s->spec_name;
                $size[$i]['spec_id'] = $s->spec_id;
                $size[$i]['values'] = $values;
            }
            if($s->type == 2){
                $color[$i]['spec_id'] = $s->spec_id;
                $color[$i]['spec_name'] = $s->spec_name;
                $color[$i]['spec_id'] = $s->spec_id;
                $color[$i]['values'] = $values;
            }
        }
        $return = ['size'=>$size,'color'=>$color];
        \Yii::$app->cache->set($cacheKey, $return, 600);
        return $return;
    }


    public function getValue()
    {
        return $this->hasMany(GoodsSpecValue::className(), ['spec_id' => 'spec_id']);
    }


    public function getAll(){
        $all = self::find()
            ->from(self::tableName())
            ->asArray()
            ->all();
        $result = [];
        foreach ($all as $v){
            $result[$v['spec_id']] = $v['spec_name'];
        }
        return $result;
    }


    public function getLanguage($lang = LANG_SET)
    {
        return $this->hasMany(GoodsLanguage::className(), ['table_id' => 'spec_id'])
            ->where(['table_field'=>'spec_name','table_name'=>'goods_spec','language'=>$lang]);
    }

}
