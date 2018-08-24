<?php

namespace app\modules\api\controllers;

use app\modules\api\models\Region;
class ExtendsController extends CommonController
{

    public function init() {
        parent::init();
        parent::behaviors();
    }


    public $country;
    /**
     * 数据统一格式输出
     * 2017年4月25日 下午4:34:03
     * @author liyee
     * @param number $code
     * @param unknown $data
     */
    protected function dataOut($code = 0, $data=[]) {
        $response = \Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $response->data = [
            'code' => $code,
            'data' => $data,
            'message' => $this->message($code),            
        ];
    }
    
    private function message($code = 0) {
        $message = [
            '0'    => 'Success!',
            '1001' => 'Missing parameter!',
            '1002' => 'The Exchange rate is not set!',
            '10001' => 'please log in first!',
        ];
        
        return isset($message[$code])?$message[$code]:'';
    }
    
    protected function country(){
        $list = [];
        $region = Region::find()->select(['id', 'region_name', 'name_zh'])->asArray()->all();
        
        if ($region){
            foreach ($region as $item){
                $list[$item['id']] = [
                    'id' => $item['id'],
                    'region_name' => $item['region_name'],
                    'name_zh' => $item['name_zh'],
                ];
            }
        }

        $default = array_filter($list, array($this, 'filter'));
        return $default?array_values($default)[0]:$list['235'];
    }
    
    /**
     * 根据国家名称筛选
     * 2016年9月13日 上午11:10:45
     * @author liyee
     * @param unknown $countries
     */
    private function filter($countries){
        return $countries['name_zh'] == $this->country;
    }

}
