<?php

namespace modules\shop\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use modules\shop\models\GoodsMarketingPostal;

/**
 * GoodsMarketingPostalSearch represents the model behind the search form about `modules\shop\models\GoodsMarketingPostal`.
 */
class GoodsMarketingPostalSearch extends GoodsMarketingPostal
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_time', 'update_time'], 'integer'],
            [['type', 'title'], 'safe'],
            [['price'], 'number'],
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
        $query = GoodsMarketingPostal::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'price' => $this->price,
            'created_time' => $this->created_time,
            'update_time' => $this->update_time,
        ]);

        $query->andFilterWhere(['like', 'type', $this->type])
            ->andFilterWhere(['like', 'title', $this->title]);

        return $dataProvider;
    }
}