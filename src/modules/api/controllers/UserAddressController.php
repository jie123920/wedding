<?php
namespace app\modules\api\controllers;

use app\helpers\myhelper;
use app\modules\api\services\UserAddress;
use Yii;

class UserAddressController extends CommonController
{
    public function beforeAction($action)
    {
        parent::behaviors();
        Yii::$app->response->format = yii\web\response::FORMAT_JSON;
        if (!$this->is_login) {
            echo json_encode($this->result(10001, [],'Please login first!'));
            Yii::$app->end();
        }
        return parent::beforeAction($action);
    }

    /**
     * 新增地址
     * @return array
     */
    public function actionCreate()
    {
        if (!Yii::$app->request->isPost) {
            return $this->result(1, [],'Request is not allowed');
        }

        $data         = Yii::$app->request->post();
        $data['uid']  = $this->getUid();
        $data['shop_id']  = 3;
        $userAddressService = new UserAddress();
        return $userAddressService->create($data);
    }

    /**
     * 删除地址
     * @return array
     */
    public function actionDelete()
    {
        if (!Yii::$app->request->isPost) {
            return $this->result(1, [],'Request is not allowed');
        }

        $data         = Yii::$app->request->post();
        $data['uid']  = $this->getUid();
        $data['shop_id']  = 3;

        $userAddressService = new UserAddress();
        return $userAddressService->delete($data);
    }

    /**
     * 编辑地址
     * @return array
     */
    public function actionEdit()
    {
        if (!Yii::$app->request->isPost) {
            return $this->result(1, [],'Request is not allowed');
        }

        $data         = Yii::$app->request->post();
        $data['uid']  = $this->getUid();
        $data['shop_id']  = 3;


        $userAddressService = new UserAddress();
        return $userAddressService->edit($data);

    }

    /**
     * 获取地址列表
     * @return array
     */
    public function actionList()
    {
        $data['uid']  = $this->getUid();
        $data['shop_id']  = 3;
        $userAddressService = new UserAddress();
        return $userAddressService->addressList($data);
    }

    /**
     * 设置为默认地址
     * @return array
     */
    public function actionDefault()
    {
        if (!Yii::$app->request->isPost) {
            return $this->result(1, [],'Request is not allowed');
        }

        $data         = Yii::$app->request->post();
        $data['uid']  = $this->getUid();
        $data['shop_id']  = 3;
        $userAddressService = new UserAddress();
        return $userAddressService->setDefault($data);
    }

    /**
     * 获取用户id
     *
     * @return int
     */
    private function getUid()
    {
        return $this->user_info['id'];
    }
}