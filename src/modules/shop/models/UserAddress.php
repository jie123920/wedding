<?php

namespace app\modules\shop\models;

use app\helpers\myhelper;
use Yii;

class UserAddress extends \yii\db\ActiveRecord
{
    const IS_NOT_DEFAULT = 0;  // 非默认地址
    const IS_DEFAULT     = 1;  // 默认地址
    const IS_NOT_CHECKED = 0;  // 非选中地址
    const IS_CHECKED     = 1;  // 选中地址

    public static function tableName()
    {
        return '{{user_address}}';
    }

    public static function getDb()
    {
        return Yii::$app->get('db_shop');
    }

    public function scenarios()
    {
        return [
            'create'  => ['uid', 'email', 'fullname', 'country_id', 'city', 'phone', 'address', 'address2', 'postal_code'],
            'update'  => ['uid', 'email', 'fullname', 'country_id', 'city', 'phone', 'address', 'address2', 'postal_code', 'is_default', 'is_checked'],
            'default' => ['uid', 'email', 'fullname', 'country_id', 'city', 'phone', 'address', 'address2', 'postal_code'],
        ];
    }

    public function rules()
    {
        return [
            [['uid', 'email', 'fullname', 'country_id', 'city', 'phone', 'address', 'postal_code'], 'required'],
            [['uid', 'country_id', 'is_default',], 'integer'],
            [['fullname', 'address', 'address2'], 'string', 'max' => 255],
            [['email', 'city', 'country', 'postal_code'], 'string', 'max' => 128],
            [['phone'], 'string', 'max' => 64],
            [['email'], 'email'],
            [['is_default', 'is_checked'], 'default', 'value' => 0],
            ['phone', 'validatePhone'],
            ['postal_code', 'match', 'pattern' => '/^[0-9]\\d{5}$/'],
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => \yii\behaviors\TimestampBehavior::className(),
                'attributes' => [
                    self::EVENT_BEFORE_INSERT => ['created_time', 'updated_time'],
                    self::EVENT_BEFORE_UPDATE => ['updated_time'],
                ],
            ],
        ];
    }

    /**
     * 自定义验证器
     * @param $attribute
     */
    public function validatePhone($attribute)
    {
        if (!myhelper::grepcheck($this->$attribute, 'intelphone') &&
            !myhelper::grepcheck($this->$attribute, 'cellphone')) {
            $this->addError($attribute, 'Invalid telephone format');
        }
    }

    public function beforeSave($insert)
    {
        $region = Region::findOne($this->country_id);
        if ($region) {
            $this->country = $region->region_name;
        }

        $count = self::find()->where(['is_default' => self::IS_DEFAULT, 'uid' => $this->uid])->count();
        if (!$count) {
            $this->is_default = self::IS_DEFAULT;
        }

        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        // 更新缓存
        $addressList = $this->getDbList($this->uid);
        $this->updateCache($this->uid, $addressList);

        return parent::afterSave($insert, $changedAttributes);
    }

    public function afterDelete()
    {
        if ($this->is_default) {
            // 删除的是默认地址
            $model = self::find()->where(['uid' => $this->uid])
                ->orderBy(['id' => SORT_DESC])
                ->one();
            if ($model) {
                $model->is_default = self::IS_DEFAULT;
                $model->save();
            }
        }
        // 更新缓存
        $addressList = $this->getDbList($this->uid);
        $this->updateCache($this->uid, $addressList);

        return parent::afterDelete();
    }

    /**
     * @param $uid
     * @return array|mixed|\yii\db\ActiveRecord[]
     */
    public function getAddressList($uid)
    {
        return $this->getCacheList($uid);
    }

    /**
     * @param $id
     * @return bool
     */
    public function setIsChecked($id)
    {
        $model = self::findOne($id);
        if ($model) {
            self::updateAll(['is_checked' => 0]);
            $model->is_checked = self::IS_CHECKED;
            return $model->save();
        }
    }

    /**
     * @param int $uid
     * @return string
     */
    private function getCacheKey($uid = 0)
    {
        return CACHE_PREFIX . '_get_user_address_uid:' . $uid;
    }

    /**
     * 更新地址缓存信息
     * @param $uid
     */
    private function updateCache($uid, $addressList)
    {
        $cacheKey = $this->getCacheKey($uid);
        if (count($addressList) > 0) {
            Yii::$app->redis->set($cacheKey, serialize($addressList));
            Yii::$app->redis->expire($cacheKey, CACHE_EXPIRE);
        } else {
            Yii::$app->redis->expire($cacheKey, 0);
        }
    }

    /**
     * 获取地址缓存信息
     * @param $uid
     * @return array|mixed|\yii\db\ActiveRecord[]
     */
    private function getCacheList($uid)
    {
        $cacheKey = $this->getCacheKey($uid);
        $addressList = Yii::$app->redis->get($cacheKey);
        if ($addressList) {
            return unserialize($addressList);
        }

        $addressList = $this->getDbList($uid);
        $this->updateCache($uid, $addressList);
        return $addressList;
    }

    /**
     * 从数据库获取会员地址列表
     *
     * @param $uid
     * @return array|\yii\db\ActiveRecord[]
     */
    private function getDbList($uid)
    {
        return self::find()
            ->where(['uid' => $uid])
            ->orderBy(['is_checked' => SORT_DESC, 'id' => SORT_DESC])
            ->asArray()
            ->all();
    }
}
