<?php
namespace app\modules\shop\models;
use Yii;
class BlockZhuanti extends \yii\db\ActiveRecord
{

    public static $URL =  [
        '1'=>[
            'dev'=>'https://testshop.clothesforever.com',
            'qa'=>'https://testshop.clothesforever.com',
            'prod'=>'https://shop.clothesforever.com'
        ],
        '2'=>[
            'dev'=>'https://testwww.lovecrunch.com',
            'qa'=>'https://testwww.lovecrunch.com',
            'prod'=>'https://www.lovecrunch.com'
        ]
    ];

    public static function getDb() {
        return Yii::$app->get('db_shop');
    }
    public static function tableName()
    {
        return 'block_zhuanti';
    }
}
