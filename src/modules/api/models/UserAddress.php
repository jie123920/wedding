<?php
/**
 * Created by IntelliJ IDEA.
 * User: shihuipeng
 * Date: 2017/7/3
 * Time: 下午3:59
 */
namespace app\modules\api\models;

use Yii;
use yii\db\ActiveRecord;

class UserAddress extends ActiveRecord
{

    public static $select = ['uid', 'email', 'full_name', 'country', 'city', 'phone', 'address1', 'address2', 'postal_code'];

    /**
     * @inheritdoc
     */
    public static function getDb()
    {
        return Yii::$app->get('db_shop');
    }

    public static function tableName()
    {
        return 'user_address';
    }

    public function scenarios() {
        return [
            'default' => ['uid', 'email', 'full_name', 'country', 'city', 'phone', 'address1', 'address2', 'postal_code'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'email', 'full_name', 'country', 'city', 'phone', 'address1', 'address2', 'postal_code'], 'required'],
        ];
    }

}