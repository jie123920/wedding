<?php

namespace app\modules\api\models;

use app\modules\api\models\Region;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * RegionSearch represents the model behind the search form about `app\modules\shop\models\Region`.
 */
class RegionSearch extends Region {

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['id', 'pid'], 'integer'],
            [['region_name', 'name_zh', 'country_code', 'area_code'], 'safe'],
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
        $query = Region::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // $query->andFilterWhere([
        //     'id'           => $this->id,
        //     'shop_id'      => $this->shop_id,
        //     'status'       => $this->status,
        //     'seq_number'   => $this->seq_number,
        //     'created_time' => $this->created_time,
        //     'updated_time' => $this->updated_time,
        // ]);

        // $query->andFilterWhere(['like', 'name', $this->name])
        //     ->andFilterWhere(['like', 'photo', $this->photo]);

        return $dataProvider;
    }
}
