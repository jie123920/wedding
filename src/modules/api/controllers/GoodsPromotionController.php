<?php

namespace app\modules\api\controllers;
use app\modules\api\models\GoodsPromotionSearch;

class GoodsPromotionController extends CommonController
{
    public function init() {
        parent::init();
        parent::behaviors();
    }

    /**
     * Lists all GoodsPromotion models.
     * @return mixed
     */
    public function actionIndex()
    {
        $data = GoodsPromotionSearch::find()->where(['shop_id'=>3])->asArray()->one();
        if (isset($data['json'])){
            $returnData['data'] = json_decode($data['json']);
        }
        return $this->result(0,$returnData,'');
    }

}
