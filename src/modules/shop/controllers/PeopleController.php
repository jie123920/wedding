<?php

namespace app\modules\shop\controllers;

use app\modules\shop\models\GoodsPeople;
use app\modules\shop\models\GoodsPeopleSearch;
use app\modules\shop\models\GoodsCategory;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * PeopleController implements the CRUD actions for GoodsPeople model.
 */
class PeopleController extends CommonController {
    public function init() {
        parent::init();
    }

    /**
     * Lists all GoodsPeople models.
     * @return mixed
     */
    public function actionIndex() {

        $all_category = (new GoodsCategory())->get_categories_tree();//获取所有分类

        $searchModel  = new GoodsPeopleSearch();
        $dataProvider = $searchModel->search(ArrayHelper::merge(Yii::$app->request->queryParams, [$searchModel->formName() => [
            'shop_id' => SHOP_ID,
            'status'  => GoodsPeople::STATUS_ENABLE,
        ]]));
        $pageSize                           = Yii::$app->request->get($dataProvider->Pagination->pageSizeParam);
        $dataProvider->Pagination->pageSize = $pageSize ? $pageSize : $dataProvider->Pagination->pageSize;

        $returnData = [
            'data' => [],
        ];
        foreach ($dataProvider->getModels() as $model) {
            $returnData['data'][] = $model->toArray();
        }

        $returnData['page'] = [
            'link'       => $dataProvider->Pagination->getLinks(),
            'totalCount' => $dataProvider->Pagination->totalCount,
            'pageSize'   => $dataProvider->Pagination->getPageSize(),
            'pageCount'  => $dataProvider->Pagination->getPageCount(),
        ];
        return $this->result(0,$returnData,'获取成功');
    }

    /**
     * Displays a single GoodsPeople model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id) {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Finds the GoodsPeople model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return GoodsPeople the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = GoodsPeople::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.', 1);
        }
    }
}
