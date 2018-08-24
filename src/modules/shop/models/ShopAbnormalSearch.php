<?php

namespace modules\shop\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use modules\shop\models\ShopAbnormal;

/**
 * ShopAbnormalSearch represents the model behind the search form about `modules\shop\models\ShopAbnormal`.
 */
class ShopAbnormalSearch extends ShopAbnormal
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status', 'updatetime', 'createtime'], 'integer'],
            [['uid', 'email', 'orderid', 'channel_orderid', 'channel', 'cahnnel_method', 'apply_amount', 'reality_amount', 'currency', 'currency_symbol', 'remark'], 'safe'],
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
        if(isset($params['ShopAbnormalSearch']['fromdate']) && isset($params['ShopAbnormalSearch']['todate'])){
            $this->fromdate = $params['ShopAbnormalSearch']['fromdate'];
            $this->todate = $params['ShopAbnormalSearch']['todate'];
        }
        
        $query = ShopAbnormal::find();

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
            'status' => $this->status,
//             'updatetime' => $this->updatetime,
//             'createtime' => $this->createtime,
        ]);
        
        if($this->fromdate!='' && $this->todate!=''){
            $query->andFilterWhere(['between', 'createtime', strtotime($this->fromdate),strtotime($this->todate)+59]);
        }

        $query->andFilterWhere(['like', 'uid', $this->uid])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'orderid', $this->orderid])
            ->andFilterWhere(['like', 'channel_orderid', $this->channel_orderid])
            ->andFilterWhere(['like', 'channel', $this->channel])
            ->andFilterWhere(['like', 'channel_method', $this->channel_method])
            ->andFilterWhere(['like', 'apply_amount', $this->apply_amount])
            ->andFilterWhere(['like', 'reality_amount', $this->reality_amount])
            ->andFilterWhere(['like', 'currency', $this->currency])
            ->andFilterWhere(['like', 'currency_symbol', $this->currency_symbol])
            ->andFilterWhere(['like', 'remark', $this->remark]);

        return $dataProvider;
    }
}
