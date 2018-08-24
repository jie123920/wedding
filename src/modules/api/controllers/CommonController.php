<?php
namespace app\modules\api\controllers;
use Yii;
use app\modules\wedding\controllers\CommonController as Comm;
class CommonController extends Comm {
    public $callback = null;

    public function behaviors()
    {
        return [
            'corsFilter' => [
                'class' => \yii\filters\Cors::className(),
                'cors' => [
                    'Origin' => [\yii::$app->params['MY_URL']['M']],
                    'Access-Control-Request-Method' => ['GET','POST', 'HEAD'],
                    'Access-Control-Request-Headers' => ['X-Wsse'],
                    'Access-Control-Allow-Credentials' => true,
                    'Access-Control-Max-Age' => 3600,
                    'Access-Control-Expose-Headers' => ['X-Pagination-Current-Page'],
                ],
            ],
        ];
    }

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
