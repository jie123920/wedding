<?php

namespace app\modules\api\models;
use yii\data\ActiveDataProvider;
use yii;
class GoodsSpecValue extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{goods_spec_values}}';
    }
    public function scenarios() {
        return [
            'create' => ['spec_value_id','spec_id','spec_value','spec_image','sort'],
            'update' => ['spec_id','spec_value','spec_image','sort'],
            'default' => ['spec_value_id','spec_id','spec_value','spec_image','sort'],
        ];
    }

    public static function getDb() {
        return Yii::$app->get('db_shop');
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['spec_id','spec_value_id', 'sort'], 'integer'],
            [['sort'], 'integer', 'min'=>0, 'max'=>99],
            [['spec_id'], 'required',  'message'=>'分类必填'],
            [['spec_value'], 'required',  'message'=>'名称必填'],
            [['spec_value'], 'safe'],
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
        $query->joinWith(['category']);
        $query->joinWith(['language']);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

//        if (!$this->validate()) {
//            return $dataProvider;
//        }

        $query->andFilterWhere([
            self::tableName().'.spec_id' => $this->spec_id,
        ]);

        //$query->andFilterWhere(['like', 'spec_name', $this->spec_name]);

        $query->orderBy('spec_value_id desc');

        return $dataProvider;
    }


    public function getOne($id){
        $query = self::find();
        $query->joinWith(['language']);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $query->andFilterWhere([
            self::tableName().'.spec_value_id' => $id,
        ]);
        return $dataProvider;
    }

    public function getCategory()
    {
        return $this->hasOne(GoodsSpec::className(), ['spec_id' => 'spec_id']);
    }
    public function getLanguage($lang = LANG_SET)
    {
        return $this->hasMany(GoodsLanguage::className(), ['table_id' => 'spec_value_id'])
            ->where(['table_field'=>'spec_value','table_name'=>'goods_spec_values','language'=>$lang]);
    }

    public function getSpecIndex()
    {
        return $this->hasMany(GoodsSpecIndex::className(), ['spec_value_id' => 'spec_value_id']);
    }

    public function getPhotos($goods_id = null)
    {
        return $this->hasMany(GoodsPhoto::className(), ['spec_value_id' => 'spec_value_id'])
            ->where(['type'=>3,'goods_id'=>$goods_id])->orderBy('place asc');
    }
}
