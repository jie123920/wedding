<?php

namespace app\modules\shop\controllers;

use yii\web\Response;
use yii\rest\Controller;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use app\helpers\myhelper;
use yii\web\ServerErrorHttpException;

class RestController extends Controller
{
    public $m;
    public $modelClass;
    public $queryParams;
    public $pageSize;
    
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'index'  => ['get'],
                    'view'  => ['get'],
                    'update' => ['put'],
                ],
            ],
        ];
    }
    
    public function init(){
        $request = \Yii::$app->request;        
        $method = $request->method;
        switch ($method)
        {
            case 'GET':
                $m = $request->get('m');
                $sign = $request->get('sign');
                $random = $request->get('random');
                $queryParams = $request->get('queryParams');
                $pageSize = $request->get('pageSize',20);
                break;
            case 'POST':
                $m = $request->get('m');
                $sign = $request->get('sign');
                $random = $request->get('random');
                $queryParams = $request->post();
                break;
            case 'PUT':
                $m = $request->get('m');
                $sign = $request->get('sign');
                $random = $request->get('random');
                $queryParams = $request->getBodyParams();
                break;
            default:                
        }
        if (myhelper::checksign($sign, $random)){
            $m = explode('-', $m);
            array_walk($m,function(&$v,$k){$v = ucwords($v);});
            $m = implode('', $m);
            $this->m = $m;
            $this->modelClass = 'app\modules\shop\models\\'.$m;
            $this->queryParams = $queryParams;
            if (isset($pageSize)){
                if ($pageSize > 300){
                    if ($m == 'PayRegion'){
                        $pageSize = 260;
                    }else {
                        $pageSize = 20;
                    }
                }
                $this->pageSize = $pageSize;
            }
            
        }else {
            $result = myhelper::code(100);
            echo json_encode($result);
            exit();
        }
    }
    
    public function beforeAction($action)
    {
        \Yii::$app->response->format = Response::FORMAT_JSONP;
        return parent::beforeAction($action);
    }
    
    public function actionIndex() {
        $model = $this->modelClass;
        $query = $model::find();
        
        if ($this->queryParams){
            $queryParams = json_decode($this->queryParams, true);        
            $queryParams = array_filter($queryParams);
    
            if ($queryParams){
                foreach ($queryParams as $key=>$item){
                    $query->andWhere([$key=>$item]);
                }
            }
        }
        
        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $this->pageSize,
            ],
        ]);
    }
    
    public function actionView($id)
    {
        $model = $this->modelClass;
        $query = $model::find();
    
        return new ActiveDataProvider([
            'query' => $query->where(['id'=>$id]),
        ]);
    }
    
    public function actionUpdate($id){
        $class = $this->modelClass;
        $model = $class::findone($id);
        $model->scenario = 'default';
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');
        
        if ($model->save() === false && !$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
        }
    
        return $model;
    }
    
}
