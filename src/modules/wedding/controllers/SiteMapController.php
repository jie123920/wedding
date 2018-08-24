<?php
namespace app\modules\wedding\controllers;

use Yii;
use yii\web\Response;
use yii\helpers\Url;
use app\modules\api\services\Goods as ServiceGoods;
use app\modules\shop\models\Goods;
class SiteMapController extends CommonController
{
    public function init()
    {
        parent::init();
        $this->layout = false;
    }

    public function actionIndex()
    {
        if (!$sitemapData = \Yii::$app->redis->get('bycouturier_sitemap')) {
            $sitemapData = $this->buildSitemap();
        }
        Yii::$app->response->format = Response::FORMAT_RAW;
        $headers = Yii::$app->response->headers;
        $headers->add('Content-Type', 'application/xml');
        $headers->add('Content-Encoding', 'gzip');
        $headers->add('Content-Length', strlen($sitemapData));
        return $sitemapData;
    }

    public function actionIndex2()
    {
         $sitemapData = $this->buildSitemap2();
        Yii::$app->response->format = Response::FORMAT_RAW;
        $headers = Yii::$app->response->headers;
        $headers->add('Content-Type', 'application/xml');
        $headers->add('Content-Encoding', 'gzip');
        $headers->add('Content-Length', strlen($sitemapData));
        return $sitemapData;

    }
     public function buildSitemap2(){
        $sitemapData = $this->renderPartial('index2.html', ['urls' => []]);
        $sitemapData = gzencode($sitemapData);
        return $sitemapData;
     }

    public function buildSitemap()
    {
        $goods = Goods::find()->distinct()->select(['goods.id','goods.updated_time','goods.name'])->join("RIGHT JOIN","goods_category_index i","i.goods_id=goods.id")->where(['i.shop_id'=>3,'goods.status'=>1])->asArray()->all();
        $cate = ServiceGoods::Categories(3);
        $cate_xml = $goods_xml = $cate_xml_tmp = $goods_xml_tmp = [];
        foreach ($cate as $_cate){
            $urltitle = preg_replace('/[^a-z0-9\s]/','',strtolower($_cate['name']));
            $urltitle = preg_replace('/\s+/','-',$urltitle);
            $cate_xml_tmp['loc'] = "https://www.bycouturier.com/$urltitle-c".$_cate['id'];
            $cate_xml_tmp['lastmod'] = date('Y-m-d',strtotime('-1 day'));
            $cate_xml_tmp['changefreq'] = 'daily';
            $cate_xml_tmp['priority'] = 0.9;
            $cate_xml[] = $cate_xml_tmp;
            if($_cate['cat_id']){
                foreach ($_cate['cat_id'] as $__cate) {
                    $urltitle = preg_replace('/[^a-z0-9\s]/','',strtolower($__cate['name']));
                    $urltitle = preg_replace('/\s+/','-',$urltitle);
                    $cate_xml_tmp['loc']        = "https://www.bycouturier.com/$urltitle-c".$__cate['id'];
                    $cate_xml_tmp['lastmod']    = date('Y-m-d',strtotime('-1 day'));
                    $cate_xml_tmp['changefreq'] = 'daily';
                    $cate_xml_tmp['priority']   = 0.8;
                    $cate_xml[] = $cate_xml_tmp;
                }
            }
        }
        foreach ($goods as $_goods){
            $urltitle = preg_replace('/[^a-z0-9\s]/','',strtolower($_goods['name']));
            $urltitle = preg_replace('/\s+/','-',$urltitle);
            $goods_xml_tmp['loc'] = "https://www.bycouturier.com/$urltitle-g".$_goods['id'];
            $goods_xml_tmp['lastmod'] = date('Y-m-d',strtotime('-1 day'));
            $goods_xml_tmp['changefreq'] = 'daily';
            $goods_xml_tmp['priority'] = 0.7;
            $goods_xml[] = $goods_xml_tmp;
        }

        $urls = array_merge($cate_xml,$goods_xml);

        $sitemapData = $this->renderPartial('index.html', ['urls' => $urls]);
        $sitemapData = gzencode($sitemapData);


        \Yii::$app->redis->set('bycouturier_sitemap',$sitemapData);
        \Yii::$app->redis->expire('bycouturier_sitemap',86400);


        return $sitemapData;
    }
}
