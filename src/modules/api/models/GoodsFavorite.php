<?php

namespace app\modules\api\models;

use Yii;

class GoodsFavorite extends \yii\db\ActiveRecord {

    public static function getDb() {
        return Yii::$app->get('db_shop');
    }

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'goods_favorite';
    }


    public function scenarios() {
        return [
            'create' => ['id','goods_id','uid','last_modify'],
            'update' => ['id','goods_id','uid','last_modify'],
            'default'=> ['id','goods_id','uid','last_modify'],
        ];
    }

    public function rules()
    {
        return [
            [['id','goods_id', 'uid','last_modify'], 'integer'],
            [['content','table_name'], 'safe'],
            [['uid','goods_id'], 'required',  'message'=>'uid,goods_id必填'],
        ];
    }

}
