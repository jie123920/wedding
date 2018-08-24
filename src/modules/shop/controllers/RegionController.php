<?php

namespace app\modules\shop\controllers;

use app\modules\shop\models\Region;
use Yii;

/**
 * RegionController implements the CRUD actions for Region model.
 */
class RegionController extends CommonController
{
    /**
     * 获取所有国家信息
     * @return array
     * @author jiangkun <jiangk@mutantbox.com>
     */
    public function actionCountries()
    {
        $countries = Region::countries();
        $region_name = array_column($countries, 'region_name');
        array_multisort($region_name, SORT_STRING, $countries);
        return $this->result(0, $countries, 'success');
    }

    /**
     * 根据 region_id 获取单独国家信息
     * @return array
     * @author jiangkun <jiangk@mutantbox.com>
     */
    public function actionGet()
    {
        $region_id = Yii::$app->request->get('region_id', 0);
        $region = Region::find()
            ->select(['id', 'region_name', 'country_code', 'area_code', 'name_zh'])
            ->where(['id' => $region_id])
            ->asArray()
            ->one();
        return $this->result(0, $region, 'success');
    }

    /**
     * 根据 region_name 或 name_zh 获取单独国家信息
     * @return array
     * @author jiangkun <jiangk@mutantbox.com>
     */
    public function actionSearch()
    {
        $region_name = Yii::$app->request->get('region_name', '');
        $name_zh     = Yii::$app->request->get('name_zh', '');
        $query = Region::find()
            ->select(['id', 'region_name', 'country_code', 'area_code', 'name_zh']);
        if ($region_name) {
            $query->andWhere(['like', 'region_name', $region_name]);
        }
        if ($name_zh) {
            $query->andWhere(['name_zh' => $name_zh]);
        }
        $region = $query->asArray()->one();
        return $this->result(0, $region, 'success');
    }
}
