<?php

namespace app\modules\wedding\controllers;
use app\helpers\myhelper;

class SupportController extends CommonController {

    public function init(){
        parent::init();
        $this->layout = '@module/views/' . GULP . '/public/main-shop.html';
    }

    public function actionIndex() {
//        $this->view->params['meta_title'] = 'Bycouturies';
//        $this->view->params['keyword'] = "Bycouturies";
//        $this->view->params['description'] = "Bycouturies";
        
        $cacheKey = CACHE_PREFIX."_".__FILE__."_".__FUNCTION__.'_'.LANG_SET.'_'.SHOP_ID.'_get-faq';
        if ($return = \Yii::$app->redis->get($cacheKey)) {
            $faq_list = unserialize($return);
        }else{
            $data = myhelper::sendRequest(['platform'=>21],'GET',true,\Yii::$app->params['MY_URL']['CF'].'/api/news/get-faq');
            $faq_list = isset($data['data'])?$data['data']:[];
            \Yii::$app->redis->set($cacheKey, serialize($faq_list));
            \Yii::$app->redis->expire($cacheKey,CACHE_EXPIRE);
        }
        $this->view->params['bread'] = [
            [
                'url'=>'',
                'name'=>\YII::t("common","Support")
            ]
        ];
        return $this->render('index.html', [
            'faq_list'=>$faq_list
        ]);
    }


    public function actionFaq()
    {
        $id = \YII::$app->request->get('id');

        $cacheKey = CACHE_PREFIX."_".__FILE__."_".__FUNCTION__.'_'.LANG_SET.'_'.SHOP_ID.'_faq_'.$id;
        if ($return = \Yii::$app->redis->get($cacheKey)) {
            $data = unserialize($return);
        }else{
            $data = myhelper::sendRequest(['id'=>$id,'platform'=>21],'GET',true,\Yii::$app->params['MY_URL']['CF'].'/api/news/info');
            $data = isset($data['data'])?$data['data']:[];
            \Yii::$app->redis->set($cacheKey, serialize($data));
            \Yii::$app->redis->expire($cacheKey,CACHE_EXPIRE);
        }

        if(!$data){
            return $this->redirect(['/']);exit;
        }


        $this->view->params['bread'] = [
            [
                'url'=>'/support',
                'name'=>\YII::t("common","Support")
            ],
            [
                'url'=>'',
                'name'=>$data['title']
            ]
        ];
        return $this->render('faq.html', [
            'article'=>$data
        ]);
    }

    
    public function actionFaqList()
    {
        $faq_id = \YII::$app->request->get('id','');
        $keyword = \YII::$app->request->get('keyword','');

        $cacheKey = CACHE_PREFIX."_".__FILE__."_".__FUNCTION__.'_'.LANG_SET.'_'.SHOP_ID.'_'.$faq_id.'_'.$keyword;
        if ($return = \Yii::$app->redis->get($cacheKey)) {
            $data = unserialize($return);
        }else{
            $data = myhelper::sendRequest(['keyword'=>$keyword,'id'=>$faq_id,'platform'=>21],'GET',true,\Yii::$app->params['MY_URL']['CF'].'/api/news/faq-list');
            $data = isset($data['data'])?$data['data']:[];
            \Yii::$app->redis->set($cacheKey, serialize($data));
            \Yii::$app->redis->expire($cacheKey,CACHE_EXPIRE);
        }

        return $this->render('faqlist.html', [
            'article_list'=>$data,
        ]);
    }

    public function actionTermsofuse()
    {
        $this->view->params['bread'] = [
            [
                'url'=>'',
                'name'=>\YII::t("common","terms")
            ]
        ];
        return $this->render('termsofuse.html', [
        ]);
    }

    public function actionPrivacypolicy()
    {
        $this->view->params['bread'] = [
            [
                'url'=>'',
                'name'=>\YII::t("common","Privacy")
            ]
        ];
        return $this->render('privacypolicy.html', [
        ]);
    }

    public function actionContact()
    {
        $this->view->params['bread'] = [
            [
                'url'=>'',
                'name'=>\YII::t("common","Contact")
            ]
        ];
        return $this->render('contact.html', [
        ]);
    }

    public function actionReturnAndRefund()
    {
        $this->view->params['bread'] = [
            [
                'url'=>'',
                'name'=>\YII::t("shop","Delivery Policy")
            ]
        ];

        $lang  = LANG_SET;
        if(!in_array($lang,['en-us','es-es','pt-pt'])){
            $lang = 'en-us';
        }

        return $this->render('deliverypolicy_'.$lang.'.html', [
        ]);
    }
}
