<?php

namespace app\modules\shop\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class SearchGoods extends Goods
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id','up_time'], 'integer'],
            [['name','created_time','price'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
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
        $query = Goods::find();
        //$query->joinWith(['category']);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['id'=>SORT_DESC]]
        ]);
        $dataProvider->setSort([
            'attributes' => [
                'up_time' => [
                    'asc' => ['up_time' => SORT_ASC],
                    'desc' => ['up_time' => SORT_DESC],
                    'label' => 'up_time'
                ],
                'price' => [
                    'asc' => ['price' => SORT_ASC],
                    'desc' => ['price' => SORT_DESC],
                    'label' => 'price'
                ],
            ]
        ]);
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        $query->andFilterWhere([
            'id' => $this->id,
            'status' => $this->status,
        ]);
        if ($this->created_time) {
            $addTimeArr    = explode('-', $this->created_time);
            $start_time = strtotime($addTimeArr[0]);
            $end_time = strtotime($addTimeArr[1])+86400;
            $query->andFilterWhere(['between', Goods::tableName().'.created_time', $start_time, $end_time]);
        }
        $query->andFilterWhere(['like', 'name', $this->name]);
        return $dataProvider;
    }
}
