<?php

namespace modules\shop\models;

class GoodsType extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{goods_type}}';
    }
    public static function getDb() {
        return Yii::$app->get('db_shop');
    }
    public function scenarios() {
        return [
            'create' => ['goods_type_id','name','goods_spec_ids','sort','disabled'],
            'update' => ['goods_type_id','name','goods_spec_ids','sort','disabled'],
            'default' => ['goods_type_id','name','goods_spec_ids','sort','disabled'],
        ];
    }
    public function rules()
    {
        return [
            [['goods_type_id'], 'integer'],
            [['sort'], 'integer', 'min'=>0, 'max'=>99],
            [['name','goods_spec_ids'], 'required'],
            [['name', 'goods_spec_ids'], 'safe'],
        ];
    }


    public function getAll(){
        $all = self::find()
            ->from(self::tableName())
            ->asArray()
            ->all();
        $result = [];
        foreach ($all as $v){
            $result[$v['goods_type_id']] = $v['name'];
        }
        return $result;
    }

    public function getGoodsCategory()
    {
        return $this->hasMany(GoodsCategory::className(), ['goods_type_id' => 'goods_type_id']);
    }
}
