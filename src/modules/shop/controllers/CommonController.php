<?php
namespace app\modules\shop\controllers;
use Yii;
use app\modules\wedding\controllers\CommonController as Comm;

class CommonController extends Comm {
    public $callback = null;
    public function init(){
        parent::init();
        $this->callback = Yii::$app->request->get('callback',null);
        Yii::$app->response->format = $this->callback ? \yii\web\Response::FORMAT_JSONP : \yii\web\Response::FORMAT_JSON;
    }
    
    public function result($code=0,$data = [],$msg=''){
        if($this->callback){
            return array(
                    'callback' => $this->callback,
                    'data' => [
                            'code' => $code,
                            'message' => $msg,
                            'data'=>$data
                    ]
            );
        }else{
            return array(
                    'code' => $code,
                    'message' => $msg,
                    'data'=>$data
            );
        }
    }
    
    public function resultNew($data){
        if (isset($data['code']) && ($data['code'] == 0)){
            return $this->result(0, $data['data'], 'success');
        }elseif (isset($data['code'])){
            return $this->result($data['code'], $data['data'], $data['message']);
        }else {
            return $this->result(1, [], 'error');
        }
    }
}
