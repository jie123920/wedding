<?php

namespace app\modules\shop\models;

use Yii;

class EmailSubscription extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{email_subscription}}';
    }

    public static function getDb()
    {
        return Yii::$app->get('db_shop');
    }

    public function scenarios() {
        return [
            'create' => ['email'],
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => \yii\behaviors\TimestampBehavior::className(),
                'attributes' => [
                    self::EVENT_BEFORE_INSERT => ['created_time'],
                ],
            ],
        ];
    }

    public function rules()
    {
        return [
            [['email'], 'required'],
            [['email'], 'email'],
            [['email'], 'unique'],
            [['email'], 'safe'],
        ];
    }
}
