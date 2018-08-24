<?php

namespace app\modules\api\models;
use yii\data\ActiveDataProvider;
use yii;
class GoodsLanguage extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{goods_language}}';
    }
    public function scenarios() {
        return [
            'create' => ['id','language','table_name','table_id','table_field','content','created_time','updated_time'],
            'update' => ['id','language','table_name','table_id','table_field','content','created_time','updated_time'],
            'default'=> ['id','language','table_name','table_id','table_field','content','created_time','updated_time'],
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
            [['id','table_id', 'created_time','updated_time'], 'integer'],
            [['content','table_name'], 'safe'],
            [['language','table_name','table_id','table_field'], 'required',  'message'=>'多语言必填选项有空缺'],
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

        if (!$this->validate()) {
            return $dataProvider;
        }

//        $query->andFilterWhere([
//            'spec_id' => $this->spec_id,
//        ]);

        //$query->andFilterWhere(['like', 'spec_name', $this->spec_name]);

        $query->orderBy('id desc');

        return $dataProvider;
    }




}
