<?php
namespace app\modules\wedding\controllers;

use app\modules\wedding\services\Subscription;
use Yii;

class SubscriptionController extends CommonController
{
    public function beforeAction($action)
    {
        Yii::$app->response->format = yii\web\response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    /**
     * 创建邮件订阅
     */
    public function actionCreate()
    {
        if (!Yii::$app->request->isPost) {
            return $this->result(1,'Request is not allowed', []);
        }

        $data = Yii::$app->request->post();

        $subscriptionService = new Subscription();
        return $subscriptionService->create($data);
    }
}