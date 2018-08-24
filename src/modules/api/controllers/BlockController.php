<?php
namespace app\modules\api\controllers;
use app\modules\wedding\services\Goods;
class BlockController extends CommonController
{
    public function init() {
        parent::init();
        parent::behaviors();
    }

    public function actionList(){

        $lang = \yii::$app->request->get("lang",'en-us');

        $data = Goods::multi_get_block_data([BLOCK_1,BLOCK_2,BLOCK_3,BLOCK_4,BLOCK_5,BLOCK_6,BLOCK_7],50,$lang);


        $number = [
            BLOCK_1=>'one',
            BLOCK_2=>'two',
            BLOCK_3=>'three',
            BLOCK_4=>'four',
            BLOCK_5=>'five',
            BLOCK_6=>'six',
            BLOCK_7=>'seven',
        ];

        $new = [];
        foreach ($data as $k=>$_data){
            $new[$number[$k]] = $_data;
        }

        return $this->result(0,$new,'获取分类成功');
    }

}
