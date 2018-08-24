<?php
namespace app\modules\shop\controllers;

use app\modules\shop\models\EmailSubscription;
use Yii;

class SubscriptionController extends CommonController
{
    /**
     * 创建邮件订阅
     * @return array
     * @author jiangkun <jiangk@mutantbox.com>
     */
    public function actionCreate()
    {
        $email= Yii::$app->request->post('email');
        $emailSubscription = EmailSubscription::find()->where(['email' => $email])->one();

        if (!$emailSubscription) {
            $data = ['email' => $email, 'created_time' => time()];
            $emailSubscription = new EmailSubscription();
            $emailSubscription->setScenario('create');
            $emailSubscription->setAttributes($data);
            $emailSubscription->save();
            if ($emailSubscription->hasErrors('email')) {
                $code = 1;
                $message = $emailSubscription->getFirstError('email');
            } else {
                $code = 0;
                $message = 'This mailbox subscription is successful';
            }
        } else {
            $code = 1;
            $message = 'This mailbox has been subscribed';
        }
        return $this->result($code, [], $message);
    }
}