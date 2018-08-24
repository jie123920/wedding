<?php

namespace app\modules\shop\controllers;

use app\modules\shop\models\GoodsTransPrice;

/**
 * TransPriceController implements the CRUD actions for TransPrice model.
 */
class TransPriceController extends CommonController
{
    public function init() {
        parent::init();
    }

    /**
     * Lists all TransPrice models.
     * @return mixed
     * @author jiangkun <jiangk@mutantbox.com>
     */
    public function actionIndex()
    {
        $returnData['data'] = GoodsTransPrice::countryInfo();
        $returnData['default'] = \app\helpers\myhelper::payinfo('default');
        return $this->result(0, $returnData,'');
    }
}
