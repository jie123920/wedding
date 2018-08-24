<?php
namespace app\modules\shop\controllers;
use YII;
class I18nController extends CommonController
{
    public function init() {
        parent::init();
    }

    public function actionGet()
    {
        $lang = YII::$app->request->get('lang','en-us');
        $data = @include_once dirname(__DIR__)."/languages/".$lang."/common.php";
        if(!$data){
            $data = @include_once dirname(__DIR__)."/languages/en-us/common.php";
            $lang = 'en-us';
        }
        $data = [
            $lang=>
                $data
        ];
        return $this->result(0,$data,'获取成功');
    }
}
