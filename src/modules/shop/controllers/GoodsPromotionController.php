<?php

namespace app\modules\shop\controllers;

use Yii;
use app\modules\shop\models\GoodsPromotion;
use app\modules\shop\models\GoodsPromotionSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

/**
 * GoodsPromotionController implements the CRUD actions for GoodsPromotion model.
 */
class GoodsPromotionController extends CommonController
{
    public function init() {
        parent::init();
    }
    
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all GoodsPromotion models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel  = new GoodsPromotionSearch();
        $dataProvider = $searchModel->search(ArrayHelper::merge(Yii::$app->request->queryParams, [$searchModel->formName() => [
            'status' => GoodsPromotion::STATUS_ENABLE,
        ]]), 1);
        $pageSize                           = Yii::$app->request->get($dataProvider->Pagination->pageSizeParam);
        $dataProvider->Pagination->pageSize = $pageSize ? $pageSize : $dataProvider->Pagination->pageSize;
        
        $returnData = [
            'data' => [],
        ];
        foreach ($dataProvider->getModels() as $model) {
            $returnData['data'] = $model->toArray();
        }
        
        if (isset($returnData['data']['json'])){
            $returnData['data'] = json_decode($returnData['data']['json']);
        }
        
        $returnData['page'] = [
            'link'       => $dataProvider->Pagination->getLinks(),
            'totalCount' => $dataProvider->Pagination->totalCount,
            'pageSize'   => $dataProvider->Pagination->getPageSize(),
            'pageCount'  => $dataProvider->Pagination->getPageCount(),
        ];
        return $this->result(0,$returnData,'');
    }

    /**
     * Displays a single GoodsPromotion model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new GoodsPromotion model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new GoodsPromotion();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->promotion_id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing GoodsPromotion model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->promotion_id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing GoodsPromotion model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the GoodsPromotion model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return GoodsPromotion the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = GoodsPromotion::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
