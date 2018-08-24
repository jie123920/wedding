<?php

namespace app\modules\api\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\api\models\ShopOrder;

/**
 * ShopOrderSearch represents the model behind the search form about `modules\shop\models\ShopOrder`.
 */
class ShopOrderSearch extends ShopOrder
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status', 'createtime'], 'integer'],
            [['uid', 'email', 'oid', 'orderid', 'channel_orderid', 'channel', 'channel_method', 'products', 'amount', 'freight', 'total_amount', 'currency', 'currency_symbol', 'refund', 'platform', 'clientip', 'shipping_address_1'], 'safe'],
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

        $query = ShopOrder::find();
        //$query->joinWith([]);
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
            'uid' => $this->uid,
            'id' => $this->id,
            'status' => $this->status,
        ]);
        $query->orderBy('id desc');
        return $dataProvider;
    }
}
