<?php

namespace app\modules\shop\models;

/**
 * This is the ActiveQuery class for [[GoodsCart]].
 *
 * @see GoodsCart
 */
class GoodsCartQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }*/

    /**
     * @inheritdoc
     * @return GoodsCart[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return GoodsCart|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
