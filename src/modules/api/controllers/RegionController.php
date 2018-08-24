<?php

namespace app\modules\api\controllers;

use app\modules\api\models\Region;
use app\modules\api\models\RegionSearch;
use Yii;
use yii\web\NotFoundHttpException;

/**
 * RegionController implements the CRUD actions for Region model.
 */
class RegionController extends CommonController {
    public function init() {
        parent::init();
        parent::behaviors();
    }

    /**
     * Lists all Region models.
     * @return mixed
     */
    public function actionIndex() {
        $searchModel                        = new RegionSearch();
        $dataProvider                       = $searchModel->search(Yii::$app->request->queryParams);
        $pageSize                           = Yii::$app->request->get($dataProvider->Pagination->pageSizeParam);
        $dataProvider->Pagination->pageSize = $pageSize ? $pageSize : $dataProvider->Pagination->pageSize;

        $returnData = ['data' => []];
        foreach ($dataProvider->getModels() as $model) {
            $returnData['data'][] = $model->toArray();
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
     * Displays a single Region model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id) {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Finds the Region model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Region the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = Region::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.', 1);
        }
    }
}
