<?php

namespace app\modules\shop\models;

use Yii;

/**
 * This is the model class for table "goods_people".
 *
 * @property string $id
 * @property string $shop_id
 * @property string $name
 * @property string $photo
 * @property integer $status
 * @property string $seq_number
 * @property string $created_time
 * @property string $updated_time
 *
 * @property GoodsPeopleItem[] $goodsPeopleItems
 */
class GoodsPeople extends \yii\db\ActiveRecord {
    const STATUS_DISABLE = 0;
    const STATUS_ENABLE  = 1;
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
        return 'goods_people';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['shop_id', 'status', 'seq_number', 'created_time', 'updated_time'], 'integer'],
            [['name', 'photo'], 'string', 'max' => 128],
        ];
    }

    public function fields() {
        return [
            'id'          => 'id',
            'shopID'      => 'shop_id',
            'name'        => 'name',
            'photo'       => 'photo',
            'status'      => 'status',
            'sort'        => 'seq_number',
            'createdTime' => 'created_time',
            'updatedTime' => 'updated_time',
            'items'       => 'items',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItems() {
        return $this->hasMany(Goods::className(), ['id' => 'item_id'])
            ->orderBy('sort desc')
            ->via('goodsPeopleItems');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGoodsPeopleItems() {
        return $this->hasMany(GoodsPeopleItem::className(), ['people_id' => 'id']);
    }
}
