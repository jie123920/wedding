<?php

namespace modules\shop\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;


class SearchGoodsType extends GoodsType
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['goods_type_id'], 'integer'],
            [['name', 'goods_spec_ids'], 'safe'],
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
        $query = self::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'goods_type_id' => $this->goods_type_id,
            'name' => $this->name,
        ]);
        $query->orderBy('goods_type_id desc');
        return $dataProvider;
    }
}
