<?php

namespace app\modules\api\models;

use app\modules\shop\models\GoodsPeople;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * GoodsPeopleSearch represents the model behind the search form about `app\modules\shop\models\GoodsPeople`.
 */
class GoodsPeopleSearch extends GoodsPeople {
    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['id', 'shop_id', 'status', 'seq_number', 'created_time', 'updated_time','style'], 'integer'],
            [['name', 'photo'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios() {
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
    public function search($params) {
        $query = GoodsPeople::find()->with('items');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => ['seq_number' => SORT_DESC]],

        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id'           => $this->id,
            'shop_id'      => $this->shop_id,
            'status'       => $this->status,
            'seq_number'   => $this->seq_number,
            'created_time' => $this->created_time,
            'updated_time' => $this->updated_time,
        ]);
        if($this->style){
            $query->andFilterWhere([
                'style'           => $this->style,
            ]);
        }
        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'photo', $this->photo]);

        return $dataProvider;
    }
}
