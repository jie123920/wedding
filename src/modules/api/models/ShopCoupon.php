<?php
namespace app\modules\api\models;

use Yii;

/**
 * This is the model class for table "shop_coupon".
 *
 * @property integer $id
 * @property string $category
 * @property string $code
 * @property string $allow_price
 * @property integer $promotion_type
 * @property string $promotion_value
 * @property integer $start_time
 * @property integer $end_time
 * @property integer $use_times
 * @property string $remark
 * @property integer $status
 */
class ShopCoupon extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_coupon';
    }

    /**
     *
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_shop');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'allow_price',
                    'promotion_value'
                ],
                'number'
            ],
            [
                [
                    'promotion_type',
                    'start_time',
                    'end_time',
                    'use_times',
                    'status'
                ],
                'integer'
            ],
            [
                [
                    'category',
                    'code',
                    'remark'
                ],
                'string',
                'max' => 255
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'category' => 'Category',
            'code' => 'Code',
            'allow_price' => 'Allow Price',
            'promotion_type' => 'Promotion Type',
            'promotion_value' => 'Promotion Value',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'use_times' => 'Use Times',
            'remark' => 'Remark',
            'status' => 'Status'
        ];
    }
}
