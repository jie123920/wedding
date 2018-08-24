<?php

namespace app\modules\shop\models;

use Yii;

/**
 * This is the model class for table "goods_promotion".
 *
 * @property string $promotion_id
 * @property string $json
 */
class GoodsPromotion extends \yii\db\ActiveRecord
{
    const STATUS_DISABLE = 0;
    const STATUS_ENABLE  = 1;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'goods_promotion';
    }

    /**
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
            [['json'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'promotion_id' => 'Promotion ID',
            'json' => 'Json',
        ];
    }
}
