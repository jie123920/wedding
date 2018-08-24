<?php

namespace app\modules\wedding\controllers;

use app\Library\curl\Curl;
use app\modules\wedding\services\TransPrice;
use Yii;

/**
 * TransPriceController implements the CRUD actions for TransPrice model.
 */
class TransPriceController extends CommonController
{
    public function beforeAction($action)
    {
        Yii::$app->response->format = yii\web\response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    /**
     * Lists all TransPrice models.
     * @return mixed
     */
    public function actionIndex()
    {
        $data = Yii::$app->request->queryParams;
        $transPriceService = new TransPrice();
        return $transPriceService->getList($data);
    }
}
