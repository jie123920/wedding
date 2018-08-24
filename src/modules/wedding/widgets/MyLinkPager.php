<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
namespace app\modules\wedding\widgets;

use Yii;
use yii\data\Pagination;
use yii\helpers\Html;
use yii\widgets\LinkPager;

class MyLinkPager extends LinkPager {
    /**
     * Renders the page buttons.
     * @return string the rendering result
     */
    protected function renderPageButtons() {
        $pageCount = $this->pagination->getPageCount();
        if ($pageCount < 2 && $this->hideOnSinglePage) {
            return '';
        }

        $buttons     = [];
        $currentPage = $this->pagination->getPage();

        // first page
        $firstPageLabel = $this->firstPageLabel === true ? '1' : $this->firstPageLabel;
        if ($firstPageLabel !== false) {
            $buttons[] = $this->renderPageButton($firstPageLabel, 0, $this->firstPageCssClass, $currentPage <= 0, false);
        }

        // prev page
        if ($this->prevPageLabel !== false) {
            if (($page = $currentPage - 1) < 0) {
                $page = 0;
            }
            $buttons[] = $this->renderPageButton($this->prevPageLabel, $page, $this->prevPageCssClass, $currentPage <= 0, false);
        }

        // internal pages
        list($beginPage, $endPage) = $this->getPageRange();
        $tmpInternalPages          = [];
        for ($i = $beginPage; $i <= $endPage; ++$i) {
            $tmpInternalPages[] = $this->renderPageButton($i + 1, $i, null, false, $i == $currentPage);
        }
        if ($this->pagination->getPageCount() > $endPage + 1) {
            $tmpInternalPages[] = $this->renderPageButton('...' . $pageCount, $pageCount - 1, $this->lastPageCssClass, $currentPage >= $pageCount - 1, false);
        }

        $tmpInternalPages[] = '<div class="load-flip"><span>' . ($this->pagination->getPage() + 1) . '</span><span>/</span><span>' . $this->pagination->getPageCount() . '</span><span style="margin-right:10px">Pages</span></div>';

        $buttons[]          = Html::tag('div', implode("\n", $tmpInternalPages), ['class' => 'load-num']);
 
        // next page
        if ($this->nextPageLabel !== false) {
            if (($page = $currentPage + 1) >= $pageCount - 1) {
                $page = $pageCount - 1;
            }
            $buttons[] = $this->renderPageButton($this->nextPageLabel, $page, $this->nextPageCssClass, $currentPage >= $pageCount - 1, false);
        }

        // last page
        $lastPageLabel = $this->lastPageLabel === true ? $pageCount : $this->lastPageLabel;
        if ($lastPageLabel !== false) {
            $buttons[] = $this->renderPageButton($lastPageLabel, $pageCount - 1, $this->lastPageCssClass, $currentPage >= $pageCount - 1, false);
        }
       
        if(isset($_COOKIE['think_language']) && $_COOKIE['think_language'] == 'de-de'){
             $thinklang = explode('-',$_COOKIE['think_language']);
            if(isset($thinklang[0]))
            $tlang  =  \yii::$app->params['MY_URL']['BS'].'/'.$thinklang[0].'/';
            if($buttons){ 
                foreach ($buttons as $key => &$but) {
                     $but = str_replace('href="/'.$thinklang[0].'/','href="'.$tlang,urldecode($but));
                }
            }
        }else{
             if($buttons){ 
                foreach ($buttons as $key => &$but) {
                     $but = str_replace('href="/','href="'.\yii::$app->params['MY_URL']['BS'].'/',urldecode($but));
                }
            }
        }
        //var_dump($buttons);exit;
        return Html::tag('div', implode("\n", $buttons), $this->options);
    }
}
