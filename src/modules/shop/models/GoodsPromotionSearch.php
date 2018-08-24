<?php

namespace app\modules\shop\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\shop\models\GoodsPromotion;

/**
 * GoodsPromotionSearch represents the model behind the search form about `app\modules\shop\models\GoodsPromotion`.
 */
class GoodsPromotionSearch extends GoodsPromotion
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['promotion_id'], 'integer'],
            [['json'], 'safe'],
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
    public function search($params, $type = '0')
    {
        $query = GoodsPromotion::find();
        if ($type == 1){
            $query->select(['json'])->andWhere(['promotion_id' => 1]);
        }

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
            'promotion_id' => $this->promotion_id,
        ]);

        $query->andFilterWhere(['like', 'json', $this->json]);

        return $dataProvider;
    }
}
