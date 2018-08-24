<?php

namespace app\modules\shop\models;

use Yii;

/**
 * This is the model class for table "region".
 *
 * @property string $id
 * @property string $region_name
 * @property string $country_code
 * @property string $area_code
 * @property string $name_zh
 * @property string $pid
 *
 * @property GoodsTransPrice[] $goodsTransPrices
 */
class Region extends \yii\db\ActiveRecord {
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
        return 'region';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['region_name', 'area_code', 'name_zh'], 'required'],
            [['pid'], 'integer'],
            [['region_name', 'name_zh'], 'string', 'max' => 40],
            [['country_code'], 'string', 'max' => 20],
            [['area_code'], 'string', 'max' => 5],
        ];
    }

    public function fields() {
        return [
            'id'          => 'id',
            'regionName'  => 'region_name',
            'countryCode' => 'country_code',
            'areaCode'    => 'area_code',
            'pid'         => 'pid',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGoodsTransPrices() {
        return $this->hasMany(GoodsTransPrice::className(), ['country_id' => 'id']);
    }
    
    /**
     * 获取所有国家
     * 2017年8月29日 上午11:40:31
     * @author liyee
     * @return mixed
     */
    public static function countries(){        
        $result = self::getDb()->cache(function () {
            return Region::find()->select(['id','region_name','country_code'])->asArray()->all();
        },60);
        
        return $result;
    }
    
    /**
     * 获取所有国家
     * 2017年8月29日 上午11:40:31
     * @author liyee
     * @return mixed
     */
    public static function countriesNew(){
        $result = self::getDb()->cache(function () {
            $region = Region::find()->select(['id','region_name','country_code'])->asArray()->all();
            $data = [];
            foreach ($region as $item){
                $data[$item['id']] = $item;
            }
            
            return $data;
        }, 3600);
            
        return $result;
    }
    
    // 获取显示国家
    public static function get_country_data($default_num = 10, $get_more = false) {
            $data =  [
                'US', 'CA', 'AU', 'FR', 'GB', 'NZ', 'CH', 'SE', 'NO', 'DK',
                'PL', 'CZ', 'BR', 'CL', 'MX', 'ZA', 'SG', 'AE', 'RU', 'JP', 'IN',    
            ];
            if ($get_more) {
                return array_slice($data, $default_num);
            }
            return array_slice($data, 0, $default_num);
    }
    
    /**
     * 获取单个国家
     * 2017年8月29日 上午11:40:13
     * @author liyee
     * @param unknown $country_id
     * @param string $key
     * @return mixed
     */
    public static function country($country_id, $key = 'region_name'){
        $result = self::getDb()->cache(function () {
            $region = Region::find()->select(['id','region_name','country_code'])->asArray()->all();
            $data = [];
            foreach ($region as $item){
                $data[$item['id']] = $item;
            }
            
            return $data;
        }, 300);
            
        if ($country_id){
            return $result[$country_id][$key];
        }else {
            return $result[235][$key];
        }
    }
    
    /**
     * 根据国家code获取单个国家
     * 2017年8月29日 上午11:40:13
     * @author liyee
     * @param unknown $country_id
     * @param string $key
     * @return mixed
     */
    public static function countryByCode($country_code, $key = 'region_name'){
        $result = self::getDb()->cache(function () {
            $region = Region::find()->select(['id','region_name','country_code'])->asArray()->all();
            $data = [];
            foreach ($region as $item){
                $data[$item['country_code']] = $item;
            }
            
            return $data;
        }, 300);
            
        if ($country_code){
            return $result[$country_code][$key];
        }else {
            return $result['US'][$key];
        }
    }
}
