<?php

namespace app\modules\api\models;

use Yii;

/**
 * This is the model class for table "goods_people_item".
 *
 * @property string $id
 * @property string $people_id
 * @property string $item_id
 * @property string $seq_number
 * @property string $created_time
 *
 * @property Goods $item
 * @property GoodsPeople $people
 */
class GoodsPeopleItem extends \yii\db\ActiveRecord {
    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb() {
        return Yii::$app->get('db_shop');
    }

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'goods_people_item';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['people_id', 'item_id', 'seq_number', 'created_time'], 'integer'],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItem() {
        return $this->hasOne(Goods::className(), ['id' => 'item_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPeople() {
        return $this->hasOne(GoodsPeople::className(), ['id' => 'people_id']);
    }
}
