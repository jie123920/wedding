<?php
namespace app\modules\shop\controllers;

use app\modules\shop\models\UserAddress;
use Yii;
use yii\helpers\ArrayHelper;

class UserAddressController extends CommonController
{
    public function beforeAction($action)
    {
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
     * @author jiangkun <jiangk@mutantbox.com>
     */
    public function actionCreate()
    {
        $data = Yii::$app->request->post();
        try {
            $model = new UserAddress();
            $model->setScenario('create');
            $model->setAttributes($data);
            $model->save();
            if ($model->hasErrors()) {
                $message = current($model->getFirstErrors()) ?: 'Create failed.';
                return $this->result(1, [], $message);
            } else {
                return $this->result(0, $model->attributes, 'Create successful.');
            }
        }catch (\Exception $e) {
            return $this->result(1, [], $e->getMessage());
        }

    }

    /**
     * 删除地址
     * @return array
     * @author jiangkun <jiangk@mutantbox.com>
     */
    public function actionDelete()
    {
        $uid = Yii::$app->request->post('uid');
        $id = Yii::$app->request->post('id');

        try {
            $model = new UserAddress();
            $addressList = $model->getAddressList($uid);
            $addressList = ArrayHelper::index($addressList, 'id');
            if (!isset($addressList[$id]) || $addressList[$id]['uid'] != $uid) {
                return $this->result(1, [], 'Have no permissions to delete.');
            }

            if ($model->findOne($id)->delete()) {
                return $this->result(0, [], 'Delete successful.');
            }
            return $this->result(1, [], 'Delete failed.');
        } catch (\Exception $e) {
            return $this->result(1, [], $e->getMessage());
        }
    }

    /**
     * 编辑地址
     * @return array
     * @author jiangkun <jiangk@mutantbox.com>
     */
    public function actionEdit()
    {
        if (!Yii::$app->request->isPost) {
            return $this->result(1, [],'Request is not allowed');
        }

        $data = Yii::$app->request->post();
        $uid  = isset($data['uid']) ? $data['uid'] : 0;
        $id = isset($data['id']) ? $data['id'] : 0;
        try {
            $model = new UserAddress();
            $addressList = $model->getAddressList($uid);
            $addressList = ArrayHelper::index($addressList, 'id');
            if (!isset($addressList[$id]) || $addressList[$id]['uid'] != $uid) {
                return $this->result(1, [],'Have no permissions to edit.');
            }
            $model = $model->findOne($id);
            $model->setScenario('update');
            $model->setAttributes($data);
            $model->save();
            if ($model->hasErrors()) {
                $message = current($model->getFirstErrors()) ?: 'Edit failed.';
                return $this->result(1, [], $message);
            } else {
                return $this->result(0, $model->attributes,  'Edit successful.');
            }
        }catch (\Exception $e) {
            return $this->result(1, [], $e->getMessage());
        }
    }

    /**
     * 获取地址列表
     * @return array
     * @author jiangkun <jiangk@mutantbox.com>
     */
    public function actionList()
    {
        $id  = Yii::$app->request->get('id');
        $uid  = Yii::$app->request->get('uid');

        $model = new UserAddress();
        $addressList = $model->getAddressList($uid);
        $defaultAddress = [];
        if ($id) {
            $model->setIsChecked($id);
            $addressList = ArrayHelper::index($addressList, 'id');
            $data = isset($addressList[$id]) ? $addressList[$id] : [];
            unset($data['uid'], $data['created_time'], $data['updated_time']);
        } else {
            foreach ($addressList as &$address) {
                unset($address['uid'], $address['created_time'], $address['updated_time']);
                if ($address['is_default'] == UserAddress::IS_DEFAULT) {
                    $defaultAddress = $address;
                    break;
                }
            }
            $data = $addressList;
        }
        return $this->result(0, ['data' => $data, 'default' => $defaultAddress], 'success');
    }

    /**
     * 设置为默认地址
     * @return array
     * @author jiangkun <jiangk@mutantbox.com>
     */
    public function actionDefault()
    {
        if (!Yii::$app->request->isPost) {
            return $this->result(1, [],'Request is not allowed');
        }

        $id = Yii::$app->request->post('id');
        $uid  = Yii::$app->request->post('uid');

        try {
            $model = new UserAddress();
            $addressList = $model->getAddressList($uid);
            $addressList = ArrayHelper::index($addressList, 'id');
            if (!isset($addressList[$id]) || $addressList[$id]['uid'] != $uid) {
                return $this->result(1, [], 'Have no permissions to set default.');
            }

            UserAddress::updateAll(['is_default' => UserAddress::IS_NOT_DEFAULT], ['<>', 'id', $id]);
            $model = $model->findOne($id);
            $model->is_default = UserAddress::IS_DEFAULT;
            if ($model->save()) {
                return $this->result(0, [], 'Set default successful.');
            }
            return $this->result(1, [], 'Set default failed.');
        } catch (\Exception $e) {
            return $this->result(1, [], $e->getMessage());
        }
    }
}