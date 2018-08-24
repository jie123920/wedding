<?php

namespace app\modules\wedding\controllers;

use app\modules\wedding\services\Goods;
use app\modules\shop\models\Goods as G;
use app\modules\shop\models\GoodsCategory;
use Yii;
use yii\data\Pagination;
use app\helpers\myhelper;
use yii\helpers\ArrayHelper;
use app\Library\Mobile_Detect;

class GoodsController extends CommonController
{
    const PageSize = 2;

    public function init()
    {
        parent::init();
        $this->layout = '@module/views/' . GULP . '/public/main-shop.html';
    }

    public function actionView()
    {

        $goods_id = (int)Yii::$app->request->get('id');
        $sku_id   = (int)Yii::$app->request->get('kid');
        $url  = Yii::$app->request->get('good');
        $data     = Goods::get($goods_id, $sku_id, THINK_RATE_M);
        if (!$data) {
            $this->redirect(['/404']);
            \YII::$app->end();
        }

        //跳到手机版
        if((new Mobile_Detect())->isMobile()){
            $Murl = yii::$app->params['MY_URL']['M']."/goods/".$data['good']['urltitle']."/".$goods_id;
            if(\yii::$app->request->queryString){
                $Murl .= "?".\yii::$app->request->queryString;
            }
            $this->redirect($Murl);
        }


        $data['url'] = $url;
        if ($data['good']['status'] == 0) {//下架
            $this->redirect(['/404']);
            \YII::$app->end();
        }
        if($url){
            if($data['good']['urltitle'] != $url){
                $this->redirect(['/404']);
                \YII::$app->end();
            }
        }

        $this->view->params['image'] = $data['good']['cover'];
        $this->view->params['keyword']     = $this->view->params['meta_title'] = ($data['good']['name'] ? $data['good']['name'] . ' | ' : '') . 'Bycouturier';
        $this->view->params['description'] = $data['good']['description'];
        $data['spec_value_ids']            = $data['spec_value_ids_bc'];
  
        if (isset($data['productObj'])) {
            foreach ($data['productObj'] as $spec_v_ids => &$productObj) {
                $temparr = explode(';',$spec_v_ids);
                sort($temparr);
                $spec_v_ids = implode(";", $temparr);
                $temparr2 = $data['spec_value_ids'];
                sort($temparr2);
                $t_spec_value_ids = implode(";", $temparr2);
                if ($spec_v_ids == $t_spec_value_ids) {
                   //var_dump($productObj['photos']);
                   //var_dump($productObj['photos2']);
                    //wdx 0521
                    if(!$productObj['photos']){
                     
                        if(isset($data['bread']) && count($data['bread']) > 0){//var_dump($data['bread']);
                            if($data['bread'][0]['id'] == 157 || $data['bread'][0]['id'] == 162){  
                                 $g_spec_is= $this->_get_goods_spec_images($data['spec_value_ids'],$data);
                                 if($g_spec_is)
                                $productObj['photos']  = $g_spec_is;
                           }
                        }
                     }
 
                    $productObj['photos'] = array_merge($productObj['photos'], $productObj['photos2']);//合并图集
                } else {
                    unset($productObj['sorted_spec_value']);
                    unset($productObj['status']);
                    unset($productObj['photos2']);
                    // unset($productObj['photos']);
                    unset($productObj['weight']);
                    unset($productObj['price_original_local']);
                    unset($productObj['price_original']);
                    unset($productObj['price_local']);
                    unset($productObj['price']);
                }
            }
        }

        $all_spec_values = [];
        foreach ($data['specValArr'] as $spec_id=>$v){
            if(in_array($data['specArr'][$spec_id]['type'],[1,2])){
                continue;
            }
            foreach ($v as $_v){
                $all_spec_values[$_v['spec_value_id']] = $_v;
            }
        }
        $data['all_spec_values'] = $all_spec_values;


        $comment = Goods::CommentList($goods_id, SHOP_ID, 1, 10, $this->uid);

        $data['comment']       = [];
        $data['comment_count'] = 0;
        if ($comment['data']['list']) {
            $data['comment']       = $comment['data']['list']['comment'];
            $data['comment_count'] = $comment['data']['list']['total_count'];
        }


        if ($data['bread']) {
            $data['top_category'] = current($data['bread']);
            $bread = [];
            foreach ($data['bread'] as $_bread) {
                $urltitle = preg_replace('/[^a-z0-9\s]/','',strtolower($_bread['name']));
                $urltitle = preg_replace('/\s+/','-',$urltitle);
                $bread[] = [
                    'url'  => "/".$urltitle . "-c" . $_bread['id'],
                    'name' => $_bread['name']
                ];
            }
            $bread[]                     = [
                'url'  => '',
                'name' => $data['good']['name']
            ];
            $this->view->params['bread'] = $bread;
        }

        $urltitle =  \yii::$app->params['MY_URL']['BS']."/".$data['good']['urltitle']."-g".$goods_id;
        $urltitle2 = $this->view->params['bs']."/".$data['good']['urltitle']."-g".$goods_id;  //wdx 0625
        $canonical =  '<link rel="canonical" href="'.$urltitle2.'">';
        $this->view->params['canonical'] = $canonical;

        $alternate_de_url =  \yii::$app->params['MY_URL']['BS']."/de/".$data['good']['urltitle']."-g".$goods_id;
        $alternate =  '<link rel="alternate"  hreflang="de" href="'.$alternate_de_url.'">'.'<link rel="alternate" hreflang="x-default" href="'.$urltitle.'">';
                $this->view->params['alternate'] = $alternate;

        return $this->render('view.html', $data);
    }

    //wdx 0521
    private function _get_goods_spec_images($spec_v_ids,$data){
        if(!$spec_v_ids || !$data ) return [];
        if(!$data['specValArr']) return [];
        $photos = [];
         //var_dump($data['specArr']);exit;
         foreach ($spec_v_ids as $key => $value) {
            foreach ($data['specValArr'] as $k => $val) {
                if($value && $val){
                     if(count($val) > 0){
                        if(isset($data['specArr'])){   
                              if($data['specArr'][$k]['type'] == 2){   
                                foreach ($val as $ke => $va) {
                                   if($va['spec_value_id'] == $value){ 
                                    //var_dump($va);
                                    //echo $va['goods_spec_image'].' ----------';
                                       if($va['goods_spec_image'])
                                    $photos[] = $va['goods_spec_image'];
                                     
                                  }
                                }
                             }
                        }
                    }
                }
            }
        }
        //var_dump($photos);
        return $photos;
    }


    public function actionFavorite()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $goods_id                   = Yii::$app->request->get('goods_id', 0);
        if ($this->is_login != 1) {
            return ['status' => -1, 'msg' => 'please login'];
        }
        $data = Goods::Favorite($goods_id, $this->user_info['id']);
        if (isset($data['code']) && $data['code'] == 0) {
            //谷歌统计<<<
            $good          = G::findOne($goods_id);
            $category_info = $category_ids = '';
            if ($good->categories) {
                $category_ids  = ArrayHelper::getColumn($good->categories, 'category_id');
                $category      = $good->categories[0];
                $category_info = GoodsCategory::findOne($category->category_id);
            }
            $langArr = ArrayHelper::map($good->language, 'table_field', 'content', 'language');
            $name    = isset($langArr[LANG_SET]['name']) ? $langArr[LANG_SET]['name'] : (isset($langArr['en-us']['name']) ? $langArr['en-us']['name'] : '');
            $name    = $name ? $name : (isset($langArr['en-us']['name']) ? $langArr['en-us']['name'] : '');
            $gtm     = [
                'name'     => $name,
                'id'       => $goods_id,
                'category' => $category_info ? $category_info->name : '',
            ];
            //谷歌统计>>>

            //更新缓存
            Goods::DelGoodsListCache(implode('-', $category_ids));

            return ['status' => $data['code'], 'msg' => 'favorite or quit successful', 'gtm' => $gtm];
        } else {
            return ['status' => $data['code'], 'msg' => $data['message']];
        }

    }

    public function actionList()
    {
        $r_id        = intval(YII::$app->request->get('r_id', 0));
        $category_id = intval(YII::$app->request->get('category_id', 0));
        $page        = YII::$app->request->get('page', 1);
        $per_page    = YII::$app->request->get('per_page', Goods::PER_PAGE);
        $type        = YII::$app->request->get('type', 'list_by_category');
        $gg_keywords = YII::$app->request->get('keyword', '');//from GOOGLE KEYWORD
        $keywords    = YII::$app->request->get('search_keyword', '');//from PC SEARCH
        $keywords    = $gg_keywords ? $gg_keywords : $keywords;

        $ads_keywords = YII::$app->request->get('ads_keyword', '');
        $order        = YII::$app->request->get('order', 'sell');
        $selectcolor  = YII::$app->request->get('selectcolor', '');
        $color        = $selectcolor ? explode('-', $selectcolor) : [];
        $selectshape  = YII::$app->request->get('selectshape', '');
        $shape        = $selectshape ? explode('-', $selectshape) : [];
        $selectlength  = YII::$app->request->get('length', '');
        $langth        = $selectlength ? explode('-', $selectlength) : [];
        $selectfeatrue  = YII::$app->request->get('featrue', '');
        $featrue        = $selectfeatrue ? explode('-', $selectfeatrue) : [];
        $selectfabric  = YII::$app->request->get('fabric', '');
        $fabric        = $selectfabric ? explode('-', $selectfabric) : [];
        $selectneckline  = YII::$app->request->get('neckline', '');
        $neckline        = $selectneckline ? explode('-', $selectneckline) : [];

        $spec        = array_merge($shape, $color,$langth,$featrue,$fabric,$neckline);
        $spec        = implode('-', $spec);

        $price_range = YII::$app->request->get('price_range', '');

        $price_range = $price_range_filter = $this->_check_price_range($price_range);
          
        //FROM GG ADS <<<
        $utm_source   = YII::$app->request->get('utm_source', '');//GG ; FB
        $utm_campaign = YII::$app->request->get('utm_campaign', '');//'white_t-shirt'
        $ads_keywords = $utm_campaign ? $utm_campaign : $ads_keywords;
        $utm_content  = YII::$app->request->get('utm_content', '');//1,2,3
        //>>>

        if ($utm_source) $type = 'list_by_gg';
        if ($keywords) $type = 'list_by_search';
        if ($ads_keywords) $type = 'list_by_keyword';
        if ($r_id) $type = 'list_by_recommend';

        //Sale分类映射
        $sale = 0;
        if(isset(\yii::$app->params['sale'][$category_id])){
            //$category_id = yii::$app->params['sale'][$category_id];
            $sale = 1;
        }

        $cate_name = 'All';

        switch ($type) {
            case 'list_by_category'://按分类
                break;
            case 'list_by_search'://按搜索
                $cate_name = $keywords;
                break;
            case 'list_by_keyword'://按广告关键词
                $cate_name = $ads_keywords;
                break;
            case 'list_by_gg'://按广告关键词
                //$cate_name = $ads_keywords;
                break;
            case 'list_by_recommend'://按推荐
                break;
        }

        $params['uid']          = isset($this->user_info['id']) ? $this->user_info['id'] : 0;
        $params['category_id']  = $category_id;
        $params['type']         = $type;
        $params['keywords']     = $keywords;
        $params['ads_keywords'] = $ads_keywords;
        $params['utm_content']  = $utm_content ? implode(",", array_reverse(explode(",", $utm_content))) : "";
        $params['utm_source']   = $utm_source;
        $params['spec']         = $spec;
        $params['price_range']  = $price_range;
        $params['order']        = $order;
        $params['page']         = $page;
        $params['per_page']     = $per_page;
        $params['per_page_show']= Goods::PER_PAGE_SHOW;
        $params['shop_id']      = SHOP_ID;
        $params['lang']         = LANG_SET;
        $params['sale']         = $sale;
        $list                   = Goods::GoodsList($params);

        if ($list['cate_name']) $cate_name = $list['cate_name'];
        $pages = [];
        if (isset($list['total_num'])) {
            $pages = new Pagination(['totalCount' => $list['total_num'], 'pageSize' => Goods::PER_PAGE_SHOW]);
        }

        if (isset($list['category_info'])) {
            $this->view->params['meta_title']  = isset($list['category_info']['meta_title']) ? $list['category_info']['meta_title'] : '';
            $this->view->params['description'] = isset($list['category_info']['description']) ? $list['category_info']['description'] : '';
            $this->view->params['keyword']     = isset($list['category_info']['keyword']) ? $list['category_info']['keyword'] : '';
        }

        if ($price_range) {//SHOP BY PRICE SEO
            $price_range = explode('_', $price_range);
            $money       = end($price_range);
            if ($money == 10000) {
                $money = "up" . $price_range[0];
            }
            $this->view->params['meta_title']  = \Yii::t('shop', 'category_' . $category_id . '_price_title_' . $money);
            $this->view->params['description'] = \Yii::t('shop', 'category_' . $category_id . '_price_description_' . $money);
            $this->view->params['keyword']     = \Yii::t('shop', 'category_' . $category_id . '_price_keywords_' . $money);
        }

        $list_filter  = Goods::ListFilter($category_id,3,$spec,$price_range_filter);
        $all_category = Goods::Categories(SHOP_ID);
        $show_color   = Goods::ShowColor();

        $spec = $spec ? explode('-', $spec) : [];

        $cates         = $category_ids = [];
        $category_name = '';
        $parent_id     = -1;

        if ($list['category_info']) {
            $category_ids  = isset($list['category_info']['assoc_category_ids']) ? $list['category_info']['assoc_category_ids'] : [];
            $parent_id     = isset($list['category_info']['parent_id']) ? $list['category_info']['parent_id'] : [];
            $father_cat_id = 0;
            if ($category_ids) {
                $pos           = array_search(min($category_ids), $category_ids);
                $father_cat_id = $category_ids[$pos];
            }

            foreach ($all_category as $_category) {
                if ($_category['id'] == $father_cat_id) {
                    $cates         = $_category['cat_id'];
                    $category_name = $_category['name'];
                    $urltitle_m = $_category['urltitle']; 
                    break;
                }
            }
                         
            $urltitle = preg_replace('/[^a-z0-9\s]/','',strtolower($category_name));
            $urltitle = preg_replace('/\s+/','-',$urltitle);
            $urltitle_m = $urltitle_m?$urltitle_m:$urltitle;
            //跳到手机版
            if((new Mobile_Detect())->isMobile()){
                $Murl = yii::$app->params['MY_URL']['M']."/".strtolower($urltitle_m)."/".$category_id;
                if(\yii::$app->request->queryString){
                    $Murl .= "?".\yii::$app->request->queryString;
                }
                $this->redirect($Murl);
            }


            $this->view->params['bread'][] = [
                'url'  => "/".$urltitle ."-c" . $father_cat_id,
                'name' => $category_name
            ];

            if ($list['category_info']['parent_id'] > 0) {
                $c_name = isset($list['category_info']['cate_name']) ? $list['category_info']['cate_name'] : $list['category_info']['name'];
                $urltitle = preg_replace('/[^a-z0-9\s]/','',strtolower($c_name));
                $urltitle = preg_replace('/\s+/','-',$urltitle);
                $this->view->params['bread'][] = [
                    'url'  => "/".$urltitle ."-c" . $list['category_info']['id'],
                    'name' => isset($list['category_info']['cate_name']) ? $list['category_info']['cate_name'] : $list['category_info']['name']
                ];
            }
        }
        if ($type == 'list_by_search') {
            $this->view->params['bread'] = [
                [
                    'url'  => '',
                    'name' => $keywords
                ]
            ];
        }


        $urltitle = \yii::$app->params['MY_URL']['BS']."/".strtolower($cate_name)."-c".$category_id;
        if(\yii::$app->request->queryString){
            $urltitle .= "?".\yii::$app->request->queryString;
        }
        $urltitle2 = $this->view->params['bs']."/".strtolower($cate_name)."-c".$category_id;;
        if(\yii::$app->request->queryString){
            $urltitle2 .= "?".\yii::$app->request->queryString;
        }
        $canonical =  '<link rel="canonical" href="'.$urltitle2.'">';
        $this->view->params['canonical'] = $canonical;

        //wdx 0517
        $show_link = 1;
        $three_tkd = 1;
        $spec_str = 'price_range,neckline,fabric,featrue,selectshape,length,selectcolor';
        $garr = YII::$app->request->get();
        if(isset($parent_id)){
            $garr['parent_id'] = $parent_id;
        }
        if($garr){
            foreach ($garr as $key => $value) {
                if($key != 'category' &&  $key != 'category_id' ){
                    if(!empty($value) && strpos($spec_str,$key) !==false){ 
                          $show_link = 0;
                    }
                 }
            }
            if($show_link != 1 && isset($garr['category_id']) && isset($garr['parent_id'])){
                if($garr['category_id'] != $garr['parent_id']){
                    $three_tkd = 0;
                }
            }
        }
        
          
          $requesturi = $_SERVER['REQUEST_URI'];

          if($requesturi){
            $tempre = explode('?',$requesturi);
            
            if(isset($tempre[1]) && $tempre[1]){  
                $requesturi  = $requesturi.'&';
                $temp_reqarr = explode('&',$tempre[1]);
                $temp_reqstr = '';
                if($temp_reqarr){
                    foreach ($temp_reqarr as $key => $value) {
                        if($value)
                        if(strpos($value,'page=') === false){
                            if($temp_reqstr)
                            $temp_reqstr .= $value.'&';
                            else
                            $temp_reqstr =  $value.'&';
                        }
                    }
                }
                $requesturi = $tempre[0].'?'.$temp_reqstr; 
            }else{
                if(strpos($requesturi,'?') ===false)
                $requesturi = $requesturi.'?';
            }
            
          }else{
            $requesturi = '?';
          }

        if($show_link != 1 || $three_tkd != 1){
            $this->_setTKD($garr,$spec_str,$list_filter['spec'],$three_tkd,$list['cate_name']);
        } 

        return $this->render('/people/index.html', [
            'data'               => $list,
            'pages'              => $pages,
            'sort'               => $order,
            'cate_name'          => $cate_name,
            'keywords'           => $keywords,
            'filter'             => $list_filter,
            'spec'               => $spec,
            'type'               => $type,
            'cates'              => $cates,
            'category_name'      => $category_name,
            'parent_id'          => $parent_id,
            'assoc_category_ids' => $category_ids,
            'show_color'         => $show_color,
            'selected_price'     => $price_range_filter,

            'selectshape'       =>$shape,
            'selectlength'       =>$langth,
            'selectfeatrue'       =>$featrue,
            'selectfabric'       =>$fabric,
            'selectneckline'       =>$neckline,
            'selectcolor'       =>$color,
            'show_link'         =>$show_link,
            'reqstr'            =>$requesturi,
            'page'              =>$page,
            'per_page_show'     =>Goods::PER_PAGE_SHOW,
            'cur_count'         =>isset($list['data'])?count($list['data']):0,
        ]);

    }

   //wdx set title keywords description
    //20180517
    private function _setTKD($getdata = [],$filer_str = '',$spec_list = [],$three_tkd=1,$category_name=''){ 
        if(empty($getdata) ) return;
        if(empty($spec_list) ) return;
        $spec_str = 'price_range,neckline,fabric,featrue,selectshape,length,selectcolor';
        $filer_str = $filer_str?$filer_str:$spec_str;
        $category = isset($getdata['category'])?$getdata['category']:0;
        $category_name = $category_name?$category_name:$category;
        $category_id = isset($getdata['category_id'])?$getdata['category_id']:0; 
        $parent_id = isset($getdata['parent_id'])?$getdata['parent_id']:0;
        if(!$category && !$category_id) return;
        //var_dump($category_name);
        foreach ($getdata as $key => $value) {

            if(!empty($value) && strpos($spec_str,$key) !==false){
                $cate_name = [];
                $fabric = '';
                if($category == 'brides' || $category_id == 150 || $parent_id == 150){
                    if($parent_id == 150 && $three_tkd !=1){

                        $cate_name[0] = $category_name . ' Bridal Dresses';
                        $cate_name[1] = $category_name . ' Bridal Gowns';
                        $cate_name[2] = $category_name . ' Wedding Dresses';
                        $cate_name[3] = $category_name . ' Wedding Gowns';
                        $cate_name[4] = $category_name . ' Dresses';
                        $cate_name[5] = $category_name . ' Gowns';
                    }else{
                        $cate_name[0] = 'Bridal Dresses';
                        $cate_name[1] = 'Bridal Gowns';
                        $cate_name[2] = 'Wedding Dresses';
                        $cate_name[3] = 'Wedding Gowns';
                        $cate_name[4] = 'Dresses';
                        $cate_name[5] = 'Gowns';
                    }
 
                    if($key == 'price_range') {
                        $valarr = explode('_',$value);
                        if($valarr[1]){
                          $this->view->params['meta_title']  = $cate_name[0].' & '.$cate_name[1].' Under $'.$valarr[1].' - Perfect Fit At Perfect Price | Bycouturier';
                          $this->view->params['keyword'] = strtolower($cate_name[0]).' under $'.$valarr[1].' , '.strtolower($cate_name[1]).' under $'.$valarr[1];
                          $this->view->params['description']     = 'Perfect fit at perfect price ! Bycouturier offers our selection of '.strtolower($cate_name[0]).' under '.$valarr[1].' in various styles , including lace , strapless , v-neck and much more .';
                        }
     
                    }elseif($key == 'length'){
                        $length = $value;
                        if($length){
                            $length = $this->_get_filter_value($spec_list,4,$length);
                            $length = str_replace("-"," ",$length);
                            $this->view->params['meta_title']  = $length.' '.$cate_name[2].' & '.$cate_name[5].' In Various Styles | Bycouturier';
                            $this->view->params['keyword'] = strtolower($length).' '.strtolower($cate_name[2]).' , '.strtolower($length).' '.strtolower($cate_name[3]);  
                            $this->view->params['description']     = 'Browse through our selection of '.strtolower($length).' '.strtolower($cate_name[2]).' at Bycouturier . Shop for latest affordable '.strtolower($length).' wedding gowns in various styles .';
                        }

                    }elseif($key == 'fabric'){
                        $fabric = $value;
                        if($fabric){
                            $fabric = $this->_get_filter_value($spec_list,5,$fabric);
                            $this->view->params['meta_title']  = $fabric.' '.$cate_name[1].' & '.$cate_name[4].' - Perfect Fit At Perfect Price | Bycouturier';
                            $this->view->params['keyword'] = strtolower($fabric).' '.strtolower($cate_name[1]).' , '.strtolower($fabric).' '.strtolower($cate_name[0]);  
                            $this->view->params['description']     = 'Searching for savings on '.strtolower($fabric).' '.$cate_name[1].' ? Buy quality '.strtolower($fabric).' '.$cate_name[0].' directly from Bycouturier dress up suppliers';

                        }
                    }elseif($key == 'neckline'){
                         $fabric = $value;
                        if($fabric){
                            $fabric = $this->_get_filter_value($spec_list,6,$fabric);
                            $this->view->params['meta_title']  = $fabric.' '.$cate_name[2].' & '.$cate_name[5].' - Perfect Fit At Perfect Price | Bycouturier';
                            $this->view->params['keyword'] = strtolower($fabric).' '.strtolower($cate_name[2]).' , '.strtolower($fabric).' '.strtolower($cate_name[3]);  
                            $this->view->params['description']     = 'Bycouturier offers perfect '.strtolower($fabric).' '.$cate_name[2].' in shape , color and other designs , for an affordable price!';
                            
                        }
                    }elseif($key == 'featrue'){
                         $fabric = $value;
                        if($fabric){
                            $fabric = $this->_get_filter_value($spec_list,7,$fabric);
                            $this->view->params['meta_title']  = 'Fashion '.$fabric.' '.$cate_name[2].' - Dress Up Petite Brides | Bycouturier';
                            $this->view->params['keyword'] = strtolower($fabric).' '.strtolower($cate_name[2]);  
                            $this->view->params['description']     =  strtolower($fabric).' '.$cate_name[2].' provide versatile options for bridesmaid wanting to match but yet be unique . Check out Bycouturier fashion and petite '.strtolower($fabric).' dress in various styles !';
                            
                        }
                    }elseif($key == 'selectcolor'){
                        $fabric = $value;
                        if($fabric){
                            $fabric = $this->_get_filter_value($spec_list,2,$fabric);
                            $this->view->params['meta_title']  = $fabric.' '.$cate_name[2].' & '.$cate_name[5].' - Customize Dresses | Bycouturier';
                            $this->view->params['keyword'] = strtolower($fabric).' '.strtolower($cate_name[3]);  
                            $this->view->params['description'] = 'Shop for beautiful '.strtolower($fabric).' '.strtolower($cate_name[3]).' at Bycouturier ! Find the perfect '.strtolower($fabric).' '.strtolower($cate_name[2]).' for your prom in a variety of long and shape styles.';
                         }
                            
                    }            

                }elseif($category == 'bridesmaids' || $category_id == 157 || $parent_id == 157){
                     if($parent_id == 157 && $three_tkd !=1){
                      
                        $cate_name[0] = $category_name . ' Bridesmaid Dresses';
                        $cate_name[1] = $category_name . ' Bridesmaid Gown';
                        $cate_name[2] = $category_name . ' Dresses';
                        $cate_name[3] = $category_name . ' Gown';
                    }else{
                        $cate_name[0] = 'Bridesmaid Dresses';
                        $cate_name[1] = 'Bridesmaid Gown';
                        $cate_name[2] = 'Dresses';
                        $cate_name[3] = 'Gown';
                    }
                    if($key == 'price_range') {
                        $valarr = explode('_',$value);
                        if($valarr[1]){
                          $this->view->params['meta_title']  = $cate_name[0].' Under $'.$valarr[1].' - Perfect Fit At Perfect Price | Bycouturier';
                          $this->view->params['keyword'] = strtolower($cate_name[0]).' under '.$valarr[1];
                          $this->view->params['description']     = 'Perfect fit at perfect price ! Bycouturier offers our selection of '.strtolower($cate_name[0]).' under '.$valarr[1].' in various styles , including lace , strapless , v-neck and much more .';
                        }
     
                    }elseif($key == 'selectcolor'){
                        $fabric = $value;
                        if($fabric){
                            $fabric = $this->_get_filter_value($spec_list,2,$fabric);
                            $this->view->params['meta_title']  = $fabric.' '.$cate_name[0].' & '.$cate_name[2].' - Customize Bridesmaid Dresses | Bycouturier'; 
                            $this->view->params['keyword'] = strtolower($fabric).' '.strtolower($cate_name[2]). ' , '.strtolower($fabric).' bridesmaid dress , '.strtolower($fabric).' bridesmaid wedding dress';  
                            $this->view->params['description'] = 'Shop for beautiful '.strtolower($fabric).' '.strtolower($cate_name[1]).' at Bycouturier ! Find the perfect '.strtolower($fabric).' bridesmaid dress for your prom in a variety of long and shape styles . If not satisfied , you can customize '.strtolower($fabric).' bridesmaid wedding dress.';
                         }
                            
                    }elseif($key == 'fabric'){
                        $fabric = $value;
                        if($fabric){
                            $fabric = $this->_get_filter_value($spec_list,5,$fabric);
                            $this->view->params['meta_title']  = $fabric.' '.$cate_name[0].' & '.$cate_name[3].' - Perfect Fit At Perfect Price | Bycouturier'; 
                            $this->view->params['keyword'] = strtolower($fabric).' '.strtolower($cate_name[0]). ' , '.strtolower($fabric).' bridesmaid dress , '.strtolower($fabric).' bridesmaid wedding dress';  
                            $this->view->params['description'] = 'Searching for savings on '.strtolower($fabric).' '.strtolower($cate_name[0]).' ? Buy quality '.strtolower($fabric).' '.strtolower($cate_name[1]).' directly from Bycouturier dress up suppliers.';
                         }                        
                    }elseif($key == 'selectshape'){
                       $fabric = $value;
                        if($fabric){
                            $fabric = $this->_get_filter_value($spec_list,3,$fabric);
                            $this->view->params['meta_title']  = $fabric.' '.$cate_name[0].' - You Love '.$cate_name[0].' | Bycouturier'; 
                            $this->view->params['keyword'] = strtolower($fabric).' '.strtolower($cate_name[0]);  
                            $this->view->params['description'] = 'Find your perfect '.strtolower($fabric).' '.strtolower($cate_name[0]).' at Bycouturier . Sort by color , shape , color and much more .  If not satisfied , you can customize '.strtolower($fabric).' '.strtolower($cate_name[0]).'.';
                         }  
                    }elseif($key == 'neckline'){
                         $fabric = $value;
                        if($fabric){
                            $fabric = $this->_get_filter_value($spec_list,6,$fabric);
                            $this->view->params['meta_title']  = $fabric.' '.$cate_name[0].' - Perfect Bridesmaid | Bycouturier';
                            $this->view->params['keyword'] = strtolower($fabric).' '.strtolower($cate_name[0]);  
                            $this->view->params['description']     = 'Bycouturier offers perfect '.strtolower($fabric).' '.$cate_name[0].' in shape , color and other designs , for an affordable price!';
                            
                        }                       
                    }elseif($key == 'length'){
                       $length = $value;
                        if($length){
                            $length = $this->_get_filter_value($spec_list,4,$length);
                            $length = str_replace("-"," ",$length);
                            $this->view->params['meta_title']  = $length.' '.$cate_name[0].' In Various Styles | Bycouturier';
                            $this->view->params['keyword'] = strtolower($length).' '.strtolower($cate_name[0]);  
                            $this->view->params['description']     = 'Browse through our selection of '.strtolower($length).' '.strtolower($cate_name[0]).' at Bycouturier . Shop for latest affordable '.strtolower($length).' bridesmaid wedding dresse in various styles .';
                        }
                    }elseif($key == 'featrue'){
                         $fabric = $value;
                        if($fabric){
                            $fabric = $this->_get_filter_value($spec_list,7,$fabric);
                            $this->view->params['meta_title']  = 'Fashion '.$fabric.' '.$cate_name[0].' - Dress Up Petite Brides | Bycouturier';
                            $this->view->params['keyword'] = strtolower($fabric).' '.strtolower($cate_name[0]);  
                            $this->view->params['description']     =  strtolower($fabric).' '.strtolower($cate_name[0]).' provide versatile options for bridesmaid wanting to match but yet be unique . Check out Bycouturier fashion and petite '.strtolower($fabric).' dress in various styles !';
                            
                        }                        
                    }

                }elseif($category == 'dresses' || $category_id == 162 || $parent_id == 162){
                      if($parent_id == 162 && $three_tkd !=1){
                      
                       
                        $cate_name[0] = $category_name . ' Dresses';
                        $cate_name[1] = $category_name . ' Gowns';
                        $cate_name[2] = $category_name . ' Wedding Dresses';
                        $cate_name[3] = $category_name . ' Wedding Gowns';
                        $cate_name[4] = $category_name . ' Dresses';
                        $cate_name[5] = $category_name . ' Gowns';
                    }else{
                        $cate_name[0] = 'Dresses';
                        $cate_name[1] = 'Gowns';
                        $cate_name[2] = 'Wedding Dresses';
                        $cate_name[3] = 'Wedding Gowns';
                        $cate_name[4] = 'Dresses';
                        $cate_name[5] = 'Gowns';
                    }
                  
                    if($key == 'price_range') {
                        $valarr = explode('_',$value);
                        if($valarr[1]){
                          $this->view->params['meta_title']  = $cate_name[0].' & '.$cate_name[1].' Under $'.$valarr[1].' - Perfect Fit At Perfect Price | Bycouturier';
                          $this->view->params['keyword'] = strtolower($cate_name[0]).' under $'.$valarr[1].' , '.strtolower($cate_name[1]).' under $'.$valarr[1];
                          $this->view->params['description']     = 'Perfect fit at perfect price ! Bycouturier offers our selection of '.strtolower($cate_name[0]).' under '.$valarr[1].' in various styles , including lace , strapless , v-neck and much more .';
                        }
     
                    }elseif($key == 'length'){
                        $length = $value;
                        if($length){
                            $length = $this->_get_filter_value($spec_list,4,$length);
                            $length = str_replace("-"," ",$length);
                            $this->view->params['meta_title']  = $length.' '.$cate_name[2].' & '.$cate_name[5].' In Various Styles | Bycouturier';
                            $this->view->params['keyword'] = strtolower($length).' '.strtolower($cate_name[2]).' , '.strtolower($length).' '.strtolower($cate_name[5]);  
                            $this->view->params['description']     = 'Browse through our selection of '.strtolower($length).' '.strtolower($cate_name[2]).' at Bycouturier . Shop for latest affordable '.strtolower($length).' '.strtolower($cate_name[5]).' gowns in various styles .';
                        }
                    }elseif($key == 'fabric'){
                       $fabric = $value;
                        if($fabric){
                            $fabric = $this->_get_filter_value($spec_list,5,$fabric);
                            $this->view->params['meta_title']  = $fabric.' '.$cate_name[1].' & '.$cate_name[0].' - Perfect Fit At Perfect Price | Bycouturier'; 
                            $this->view->params['keyword'] = strtolower($fabric).' '.strtolower($cate_name[1]). ' , '.strtolower($fabric).' '.strtolower($cate_name[0]);  
                            $this->view->params['description'] = 'Searching for savings on '.strtolower($fabric).' '.strtolower($cate_name[1]).' ? Buy quality '.strtolower($fabric).' '.strtolower($cate_name[0]).' directly from Bycouturier dress up suppliers.';
                         }                        
                    }elseif($key == 'selectshape'){
                       $fabric = $value;
                        if($fabric){
                            $fabric = $this->_get_filter_value($spec_list,3,$fabric);
                            $this->view->params['meta_title']  = $fabric.' '.$cate_name[1].' & '.$cate_name[0].'- You Love '.$cate_name[1].' | Bycouturier'; 
                            $this->view->params['keyword'] = strtolower($fabric).' '.strtolower($cate_name[1]).','.strtolower($fabric).' '.strtolower($cate_name[0]);  
                            $this->view->params['description'] = 'Find your perfect '.strtolower($fabric).' '.strtolower($cate_name[1]).' at Bycouturier . Sort by color , shape , color and much more .  If not satisfied , you can customize '.strtolower($fabric).' '.strtolower($cate_name[0]).'.';
                         }  
                    }elseif($key == 'neckline'){
                        $fabric = $value;
                        if($fabric){
                            $fabric = $this->_get_filter_value($spec_list,6,$fabric);
                            $this->view->params['meta_title']  = $fabric.' '.$cate_name[0].' & '.$cate_name[1].' - Perfect Bridesmaid | Bycouturier';
                            $this->view->params['keyword'] = strtolower($fabric).' '.strtolower($cate_name[0]).','.strtolower($fabric).' '.strtolower($cate_name[1]);  
                            $this->view->params['description']     = 'Bycouturier offers perfect '.strtolower($fabric).' '.$cate_name[0].' in shape , color and other designs , for an affordable price!';
                            
                        }                               
                    }elseif($key == 'featrue'){
                         $fabric = $value;
                        if($fabric){
                            $fabric = $this->_get_filter_value($spec_list,7,$fabric);
                            $this->view->params['meta_title']  = 'Fashion '.$fabric.' '.$cate_name[0].' & '.$cate_name[1].' - Dress Up Petite Brides | Bycouturier';
                            $this->view->params['keyword'] = strtolower($fabric).' '.strtolower($cate_name[0]).','.strtolower($fabric).' '.strtolower($cate_name[1]);  
                            $this->view->params['description']     =  strtolower($fabric).' '.strtolower($cate_name[0]).' provide versatile options for bridesmaid wanting to match but yet be unique . Check out Bycouturier fashion and petite '.strtolower($fabric).' '.strtolower($cate_name[1]).' in various styles !';
                         } 
                    }elseif($key == 'selectcolor'){
                       $fabric = $value;
                        if($fabric){
                            $fabric = $this->_get_filter_value($spec_list,2,$fabric);
                            $this->view->params['meta_title']  = $fabric.' '.$cate_name[0].' & '.$cate_name[1].' - Customize Bridesmaid Dresses | Bycouturier'; 
                            $this->view->params['keyword'] = strtolower($fabric).' '.strtolower($cate_name[1]). ' , '.strtolower($fabric).' '.strtolower($cate_name[0]);  
                            $this->view->params['description'] = 'Shop for beautiful '.strtolower($fabric).' '.strtolower($cate_name[1]).' at Bycouturier ! Find the perfect '.strtolower($fabric).' '.strtolower($cate_name[0]).' for your prom in a variety of long and shape styles . If not satisfied , you can customize '.strtolower($fabric).' '.strtolower($cate_name[0]);
                         }
                            
                    }


                }elseif($category == 'celebrity-dresses' || $category_id == 214 || $parent_id == 214){  /* celebrity-dresses start*/
                    if($parent_id == 214 && $three_tkd !=1){
                        $cate_name[0] =  $category_name . ' Celebrity Dresses';
                        $cate_name[1] =  $category_name . ' Celebrity Gowns';
                        $cate_name[2] =  $category_name . ' Celebrity Dresses';
                        $cate_name[3] =  $category_name . ' Celebrity Gowns';
                        $cate_name[4] =  $category_name . ' Dresses';
                        $cate_name[5] =  $category_name . ' Gowns';
                    }else{
                        $cate_name[0] = 'Celebrity Dresses';
                        $cate_name[1] = 'Celebrity Gowns';
                        $cate_name[2] = 'Celebrity Dresses';
                        $cate_name[3] = 'Celebrity Gowns';
                        $cate_name[4] = 'Dresses';
                        $cate_name[5] = 'Gowns';
                    }
                   

                    if($key == 'price_range') {
                        $valarr = explode('_',$value);
                        if($valarr[1]){
                          $this->view->params['meta_title']  =  'Celebrity Evening Dresses & Bridal Gowns Under $'.$valarr[1].' - Perfect Fit At Perfect Price | Bycouturier';
                          $this->view->params['keyword'] = strtolower($cate_name[0]).' under $'.$valarr[1].' , '.strtolower($cate_name[1]).' under $'.$valarr[1];
                          $this->view->params['description']     = 'Perfect fit at perfect price ! Bycouturier offers our selection of '.strtolower($cate_name[0]).' under '.$valarr[1].' in various styles , including lace , strapless , v-neck and much more .';
                        }
     
                    }elseif($key == 'length'){
                        $length = $value;
                        if($length){
                            $length = $this->_get_filter_value($spec_list,4,$length);
                            $length = str_replace("-"," ",$length);
                            $this->view->params['meta_title']  = $length.' '.$cate_name[2].' & '.$cate_name[5].' In Various Styles | Bycouturier';
                            $this->view->params['keyword'] = strtolower($length).' '.strtolower($cate_name[2]).' , '.strtolower($length).' '.strtolower($cate_name[5]);  
                            $this->view->params['description']     = 'Browse through our selection of '.strtolower($length).' '.strtolower($cate_name[2]).' at Bycouturier . Shop for latest affordable '.strtolower($length).' '.strtolower($cate_name[5]).' gowns in various styles .';
                        }
                    }elseif($key == 'fabric'){
                       $fabric = $value;
                        if($fabric){
                            $fabric = $this->_get_filter_value($spec_list,5,$fabric);
                            $this->view->params['meta_title']  = $fabric.' '.$cate_name[1].' & '.$cate_name[0].' - Perfect Fit At Perfect Price | Bycouturier'; 
                            $this->view->params['keyword'] = strtolower($fabric).' '.strtolower($cate_name[1]). ' , '.strtolower($fabric).' '.strtolower($cate_name[0]);  
                            $this->view->params['description'] = 'Searching for savings on '.strtolower($fabric).' '.strtolower($cate_name[1]).' ? Buy quality '.strtolower($fabric).' '.strtolower($cate_name[0]).' directly from Bycouturier dress up suppliers.';
                         }                        
                    }elseif($key == 'selectshape'){
                       $fabric = $value;
                        if($fabric){
                            $fabric = $this->_get_filter_value($spec_list,3,$fabric);
                            $this->view->params['meta_title']  = $fabric.' '.$cate_name[1].' & '.$cate_name[0].'- You Love '.$cate_name[1].' | Bycouturier'; 
                            $this->view->params['keyword'] =  strtolower($fabric).' '.strtolower($cate_name[0]);  
                            $this->view->params['description'] = 'Find your perfect '.strtolower($fabric).' '.strtolower($cate_name[1]).' at Bycouturier . Sort by color , shape , color and much more .  If not satisfied , you can customize '.strtolower($fabric).' '.strtolower($cate_name[0]).'.';
                         }  
                    }elseif($key == 'neckline'){
                        $fabric = $value;
                        if($fabric){
                            $fabric = $this->_get_filter_value($spec_list,6,$fabric);
                            $this->view->params['meta_title']  = $fabric.' '.$cate_name[0].' & '.$cate_name[1].' - Perfect Bridesmaid | Bycouturier';
                            $this->view->params['keyword'] = strtolower($fabric).' '.strtolower($cate_name[0]).','.strtolower($fabric).' '.strtolower($cate_name[1]);  
                            $this->view->params['description']     = 'Bycouturier offers perfect '.strtolower($fabric).' '.$cate_name[0].' in shape , color and other designs , for an affordable price!';
                            
                        }                               
                    }elseif($key == 'featrue'){
                         $fabric = $value;
                        if($fabric){
                            $fabric = $this->_get_filter_value($spec_list,7,$fabric);
                            $this->view->params['meta_title']  = 'Fashion '.$fabric.' '.$cate_name[0].' & '.$cate_name[1].' - Dress Up Petite Brides | Bycouturier';
                            $this->view->params['keyword'] = strtolower($fabric).' '.strtolower($cate_name[0]);  
                            $this->view->params['description']     =  strtolower($fabric).' '.strtolower($cate_name[0]).' provide versatile options for bridesmaid wanting to match but yet be unique . Check out Bycouturier fashion and petite '.strtolower($fabric).' '.strtolower($cate_name[1]).' in various styles !';
                         } 
                    }elseif($key == 'selectcolor'){
                       $fabric = $value;
                        if($fabric){
                            $fabric = $this->_get_filter_value($spec_list,2,$fabric);
                            $this->view->params['meta_title']  = $fabric.' '.$cate_name[0].' & '.$cate_name[1].' - Customize '.$cate_name[0].' | Bycouturier'; 
                            $this->view->params['keyword'] = strtolower($fabric).' '.strtolower($cate_name[1]). ' , '.strtolower($fabric).' '.strtolower($cate_name[0]);  
                            $this->view->params['description'] = 'Shop for beautiful '.strtolower($fabric).' '.strtolower($cate_name[1]).' at Bycouturier ! Find the perfect '.strtolower($fabric).' '.strtolower($cate_name[0]).' for your prom in a variety of long and shape styles . If not satisfied , you can customize '.strtolower($fabric).' '.strtolower($cate_name[0]);
                         }
                            
                    }
                }/* celebrity-dresses end*/
                elseif($category == 'flower-girl-dresses' || $category_id == 165 || $parent_id == 165){   /* flower-girl-dresses end*/
                    if($parent_id == 165 && $three_tkd !=1){
                        
                        $cate_name[0] = $category_name . ' Flower Girl Dresses';
                        $cate_name[1] = $category_name . ' Flower Girl Gown';
                        $cate_name[2] = $category_name . ' Dresses';
                        $cate_name[3] = $category_name . ' Gown';
                        $cate_name[4] = $category_name . ' Dresses';
                        $cate_name[5] = $category_name . ' Gowns';
                    }else{
                        $cate_name[0] = 'Flower Girl Dresses';
                        $cate_name[1] = 'Flower Girl Gown';
                        $cate_name[2] = 'Dresses';
                        $cate_name[3] = 'Gown';
                        $cate_name[4] = 'Dresses';
                        $cate_name[5] = 'Gowns';
                    }
                

                    if($key == 'price_range') {
                        $valarr = explode('_',$value);
                        if($valarr[1]){
                          $this->view->params['meta_title']  =  $cate_name[0].' Under $'.$valarr[1].' - Perfect Fit At Perfect Price | Bycouturier';
                          $this->view->params['keyword'] = strtolower($cate_name[0]).' under $'.$valarr[1].' , '.strtolower($cate_name[0]).' under $'.$valarr[1];
                          $this->view->params['description']     = 'Perfect fit at perfect price ! Bycouturier offers our selection of '.strtolower($cate_name[0]).' under '.$valarr[1].' in various styles , including lace , strapless , v-neck and much more .';
                        }
     
                    }elseif($key == 'length'){
                        $length = $value;
                        if($length){
                            $length = $this->_get_filter_value($spec_list,4,$length);
                            $length = str_replace("-"," ",$length);
                            $this->view->params['meta_title']  = $length.' '.$cate_name[0].' In Various Styles | Bycouturier';
                            $this->view->params['keyword'] = strtolower($length).' '.strtolower($cate_name[0]).' , '.strtolower($length).' '.strtolower($cate_name[5]);  
                            $this->view->params['description']     = 'Browse through our selection of '.strtolower($length).' '.strtolower($cate_name[0]).' at Bycouturier . Shop for latest affordable '.strtolower($length).' '.strtolower($cate_name[0]).' gowns in various styles .';
                        }
                    }elseif($key == 'fabric'){
                       $fabric = $value;
                        if($fabric){
                            $fabric = $this->_get_filter_value($spec_list,5,$fabric);
                            $this->view->params['meta_title']  = $fabric.' '.$cate_name[0].' & '.$cate_name[3].' - Perfect Fit At Perfect Price | Bycouturier'; 
                            $this->view->params['keyword'] = strtolower($fabric).' '.strtolower($cate_name[0]). ' , '.strtolower($fabric).' '.strtolower($cate_name[1]);  
                            $this->view->params['description'] = 'Searching for savings on '.strtolower($fabric).' '.strtolower($cate_name[0]).' ? Buy quality '.strtolower($fabric).' '.strtolower($cate_name[1]).' directly from Bycouturier dress up suppliers.';
                         }                        
                    }elseif($key == 'selectshape'){
                       $fabric = $value;
                        if($fabric){
                            $fabric = $this->_get_filter_value($spec_list,3,$fabric);
                            $this->view->params['meta_title']  = $fabric.' '.$cate_name[0].' - You Love '.$cate_name[1].' | Bycouturier'; 
                            $this->view->params['keyword'] =  strtolower($fabric).' '.strtolower($cate_name[0]);  
                            $this->view->params['description'] = 'Find your perfect '.strtolower($fabric).' '.strtolower($cate_name[0]).' at Bycouturier . Sort by color , shape , color and much more .  If not satisfied , you can customize '.strtolower($fabric).' '.strtolower($cate_name[0]).'.';
                         }  
                    }elseif($key == 'neckline'){
                        $fabric = $value;
                        if($fabric){
                            $fabric = $this->_get_filter_value($spec_list,6,$fabric);
                            $this->view->params['meta_title']  = $fabric.' '.$cate_name[0].' - Perfect Flower Girl Dresses | Bycouturier';
                            $this->view->params['keyword'] = strtolower($fabric).' '.strtolower($cate_name[0]);  
                            $this->view->params['description']     = 'Bycouturier offers perfect '.strtolower($fabric).' '.$cate_name[0].' in shape , color and other designs , for an affordable price!';
                            
                        }                               
                    }elseif($key == 'featrue'){
                         $fabric = $value;
                        if($fabric){
                            $fabric = $this->_get_filter_value($spec_list,7,$fabric);
                            $this->view->params['meta_title']  = 'Fashion '.$fabric.' '.$cate_name[0].' - Dress Up Petite Brides | Bycouturier';
                            $this->view->params['keyword'] = strtolower($fabric).' '.strtolower($cate_name[0]);  
                            $this->view->params['description']     =  strtolower($fabric).' '.strtolower($cate_name[0]).' provide versatile options for bridesmaid wanting to match but yet be unique . Check out Bycouturier fashion and petite '.strtolower($fabric).' '.strtolower($cate_name[0]).' in various styles !';
                         } 
                    }elseif($key == 'selectcolor'){
                       $fabric = $value;
                        if($fabric){
                            $fabric = $this->_get_filter_value($spec_list,2,$fabric);
                            $this->view->params['meta_title']  = $fabric.' '.$cate_name[0].' & '.$cate_name[3].' - Customize '.$cate_name[0].' | Bycouturier'; 
                            $this->view->params['keyword'] = strtolower($fabric).' '.strtolower($cate_name[1]). ' , '.strtolower($fabric).' '.strtolower($cate_name[0]);  
                            $this->view->params['description'] = 'Shop for beautiful '.strtolower($fabric).' '.strtolower($cate_name[0]).' at Bycouturier ! Find the perfect '.strtolower($fabric).' '.strtolower($cate_name[0]).' for your prom in a variety of long and shape styles . If not satisfied , you can customize '.strtolower($fabric).' '.strtolower($cate_name[0]);
                         }
                            
                    }


                }elseif($category == 'accessories' || $category_id == 167 || $parent_id == 167){
                    if($parent_id == 167 && $three_tkd !=1){
                         
                        $cate_name[0] = $category_name . ' Accessories';
                        $cate_name[1] = $category_name . ' Accessories';
                        $cate_name[2] = $category_name . ' Accessories';
                        $cate_name[3] = $category_name . ' Accessories';
                        $cate_name[4] = $category_name . ' Accessories';
                        $cate_name[5] = $category_name . ' Accessories';
                    }else{
                        $cate_name[0] = 'Accessories';
                        $cate_name[1] = 'Accessories';
                        $cate_name[2] = 'Accessories';
                        $cate_name[3] = 'Accessories';
                        $cate_name[4] = 'Accessories';
                        $cate_name[5] = 'Accessories';
                    }
                   

                    if($key == 'price_range') {
                        $valarr = explode('_',$value);
                        if($valarr[1]){
                          $this->view->params['meta_title']  =  $cate_name[0].' Under $'.$valarr[1].' - Perfect Fit At Perfect Price | Bycouturier';
                          $this->view->params['keyword'] = strtolower($cate_name[0]).' under $'.$valarr[1].' , '.strtolower($cate_name[0]).' under $'.$valarr[1];
                          $this->view->params['description']     = 'Perfect fit at perfect price ! Bycouturier offers our selection of '.strtolower($cate_name[0]).' under '.$valarr[1].' in various styles , including lace , strapless , v-neck and much more .';
                        }
     
                    }elseif($key == 'selectcolor'){
                       $fabric = $value;
                        if($fabric){
                            $fabric = $this->_get_filter_value($spec_list,2,$fabric);
                            $this->view->params['meta_title']  = $fabric.' '.$cate_name[0].' - Customize '.$cate_name[0].' | Bycouturier'; 
                            $this->view->params['keyword'] = strtolower($fabric).' '.strtolower($cate_name[1]);  
                            $this->view->params['description'] = 'Shop for beautiful '.strtolower($fabric).' '.strtolower($cate_name[0]).' at Bycouturier ! Find the perfect '.strtolower($fabric).' '.strtolower($cate_name[0]).' for your prom in a variety of long and shape styles . If not satisfied , you can customize '.strtolower($fabric).' '.strtolower($cate_name[0]);
                         }
                            
                    }




                    
                }elseif($category == 'gifts-decor' || $category_id == 182 || $parent_id == 182){
                    if($parent_id == 182 && $three_tkd !=1){
                        $cate_name[0] = $category_name . ' Gifts & Decorations';
                    }else{
                        $cate_name[0] = 'Gifts & Decorations';
                    }
                   
                     if($key == 'selectcolor'){
                       $fabric = $value;
                        if($fabric){
                            $fabric = $this->_get_filter_value($spec_list,2,$fabric);
                            $this->view->params['meta_title']  = $fabric.' '.$cate_name[0].' - Customize '.$cate_name[0].' | Bycouturier'; 
                            $this->view->params['keyword'] = strtolower($fabric).' '.strtolower($cate_name[0]);  
                            $this->view->params['description'] = 'Shop for beautiful '.strtolower($fabric).' '.strtolower($cate_name[0]).' at Bycouturier ! Find the perfect '.strtolower($fabric).' '.strtolower($cate_name[0]).' for your prom in a variety of long and shape styles . If not satisfied , you can customize '.strtolower($fabric).' '.strtolower($cate_name[0]);
                         }
                            
                    }


                } elseif($category == 'mens-boys' || $category_id == 222 || $parent_id == 222){
                    if($parent_id == 222 && $three_tkd !=1){
                        $cate_name[0] = $category_name;
                        $cate_name[1] = $category_name;
                        if($category_id == 224){
                            $cate_name[2] = 'wedding suits for men';
                        }else{
                           $cate_name[2] = $category_name; 
                        }
                        
                    }else{
                        $cate_name[0] = "Men's & Boys' Suits";
                        $cate_name[1] = "mens suit";
                        $cate_name[2] = "boys suits";
                    }
                   
                     if($key == 'selectcolor'){
                       $fabric = $value;
                        if($fabric){
                            $fabric = $this->_get_filter_value($spec_list,2,$fabric);
                            $this->view->params['meta_title']  = $fabric.' '.$cate_name[0].' - Customize '.$cate_name[0].' | Bycouturier'; 
                            $this->view->params['keyword'] = strtolower($fabric).' '.strtolower($cate_name[1]).' , '.strtolower($fabric).' '.strtolower($cate_name[2]);  
                            $this->view->params['description'] = 'Shop for beautiful '.strtolower($fabric).' '.strtolower($cate_name[0]).' at Bycouturier ! Find the perfect '.strtolower($fabric).' '.strtolower($cate_name[0]).' for your prom in a variety of long and shape styles .';
                         }
                            
                    }elseif($key == 'fabric'){
                       $fabric = $value;
                        if($fabric){
                            $fabric = $this->_get_filter_value($spec_list,5,$fabric);
                            $this->view->params['meta_title']  = $fabric.' '.$cate_name[0].' - Perfect Fit At Perfect Price | Bycouturier'; 
                            $this->view->params['keyword'] = strtolower($fabric).' '.strtolower($cate_name[1]). ' , '.strtolower($fabric).' '.strtolower($cate_name[2]);  
                            $this->view->params['description'] = 'Searching for savings on '.strtolower($fabric).' '.strtolower($cate_name[0]).' ? Buy quality '.strtolower($fabric).' '.strtolower($cate_name[1]).' directly from Bycouturier dress up suppliers.';
                         }                        
                    }


                } 


            }
        }
    }


    private function _get_filter_value($spec_list,$type,$vid){
        if(empty($vid)) return '';
        $vid = abs($vid);
        if($spec_list)
        foreach($spec_list as $spec){
            if($spec['type'] == $type && $spec['spec_value_id'] == $vid){
                return $spec['spec_value'];
            }
        }
        return '';
    }


    private function _check_price_range($price_range)
    {
        $price_range_    = [];
        $new_price_range = '';
        if ($price_range) {
            $price_range = explode('-', $price_range);
            foreach ($price_range as $i => $_price_range) {
                $_price_range = explode('_', $_price_range);
                if ($_price_range && count($_price_range) == 2 && isset($_price_range[0]) && isset($_price_range[1])) {
                    if (intval($_price_range[1]) > intval($_price_range[0])) {
                        $price_range_[$i] = intval($_price_range[0]) . '_' . intval($_price_range[1]);
                    }
                }
            }
            $new_price_range = implode('-', $price_range_);
        }
        return $new_price_range;
    }

    public function actionMyFavorite()
    {
        $this->check_user('/my-favorite');

        $this->view->params['active_shop'] = '1';
        $order                             = YII::$app->request->get('order', '');
        $page                              = YII::$app->request->get('page', 1);
        $per_page                          = YII::$app->request->get('per_page', Goods::PER_PAGE);

        switch ($order) {
            case 'price':
                $orderBy = 'price ASC';
                break;
            case '-price':
                $orderBy = 'price DESC';
                break;
            case 'up_time':
                $orderBy = 'up_time DESC';
                break;
            case 'sell':
                $orderBy = 'sell DESC';
                break;
            default :
                $orderBy = 'up_time DESC';
                break;
        }

        $sort_url = "?page=" . $page;
        $list     = Goods::MyFavorite($this->user_info['id'], $orderBy, LANG_SET, $page, SHOP_ID, $per_page);
        $pages    = [];
        if ($list) {
            $pages = new Pagination(['totalCount' => $list['total_num'], 'pageSize' => $per_page]);
        }

        //echo '<pre>';print_r($list);exit;
        return $this->render('/people/myfavorite.html', [
            'data'      => $list,
            'pages'     => $pages,
            'sort_url'  => $sort_url,
            'sort'      => $order,
            'keywords'  => '',
            'cate_name' => '',
        ]);

    }

    public function actionSpecial()
    {
        $page     = \Yii::$app->request->get('page', 1);
        $per_page = \Yii::$app->request->get('per_page', Goods::PER_PAGE);
        $id       = \Yii::$app->request->get('id', 0);
        $cid      = \Yii::$app->request->get('cid', 0);

        $data = Goods::Special($id, $cid, LANG_SET, $page, $per_page, $this->uid);
        if (!$data['block']) {
            return $this->redirect(['/']);
            \Yii::$app->end();
        }
        if ($data['block']['status'] == 0) {
            return $this->redirect(['/']);
            \Yii::$app->end();
        }
        $this->view->params['meta_title']  = ($data['block']['name'] ? $data['block']['name'] . ' | ' : '') . 'Bycouturier ';
        $this->view->params['keyword']     = "";
        $this->view->params['description'] = $data['block']['name'] ? $data['block']['desc'] : '';


        $pages = [];
        if ($data['data']) {
            $pages = new Pagination(['totalCount' => $data['data']['total_num'], 'pageSize' => Goods::PER_PAGE]);
        }


        $this->view->params['bread'] = [
            [
                'url'  => '',
                'name' => $data['block']['name']
            ]
        ];
        return $this->render('/people/special.html', [
            'block' => $data['block'],
            'data'  => $data['data']['data'],
            'pages' => $pages,
        ]);
    }

    public function actionComment()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if ($this->is_login != 1) {
            return ['code' => -1, 'msg' => 'please login'];
        }

//        if(\Yii::$app->cache->get(CACHE_PREFIX.'_comment_'.$this->user_info['id'])){
//            return ['code'=>1,'msg'=>'The operating interval must not be less than 60 seconds!'];
//        }

        $goods_id = Yii::$app->request->post('goods_id', 0);
        $star     = Yii::$app->request->post('star', 5);
        $content  = Yii::$app->request->post('content', '');
        $picture  = Yii::$app->request->post('picture', []);
        $picture  = json_encode($picture);
        $data     = Goods::Comment($goods_id, SHOP_ID, $content, $star, $picture, $this->user_info['id'], $this->user_info['username'], $this->user_info['avatar']);
        if ($data && isset($data['code'])) {
            if ($data['code'] == 0) {
                //更新商品缓存
                Goods::DelGoodDetailCache($goods_id);
                //更新评论列表缓存
                Goods::DelCommentCache($goods_id);
                //每次评论的间隔是60秒
                //\Yii::$app->cache->set(CACHE_PREFIX.'_comment_'.$this->user_info['id'],1,60);

                return ['code' => 0, 'msg' => 'comment successful'];
            } else {
                return ['code' => $data['code'], 'msg' => $data['message']];
            }
        }
        return ['code' => 1, 'msg' => 'unknown error'];
    }

    public function actionMoreComment()
    {
        $this->layout = false;
        //Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $good_id  = \yii::$app->request->get('goods_id', 0);
        $page     = \yii::$app->request->get('page', 1);
        $per_page = \yii::$app->request->get('per_page', 10);
        $data     = Goods::CommentList($good_id, SHOP_ID, $page, $per_page, $this->uid);
        return $this->render('comment_list.html', $data['data']['list']);
    }

    public function actionUpload()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if (!isset($_FILES['file'])) {
            return ['data' => [], 'code' => 2];
        }
        if ($_FILES['file']['error'] > 0) {
            return ['data' => [], 'code' => 3];
        }
        $img_name = explode('.', $_FILES['file']['name']);
        if (!strstr('png|bmp|jpg|jpeg', strtolower(end($img_name)))) {
            return ['data' => [], 'code' => 1];
        }
        $img_url = myhelper::upload($_FILES['file']['tmp_name']);
        unset($_FILES);
        return ['data' => $img_url, 'code' => 0];
    }

    public function actionCartCount()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $goods_id = Yii::$app->request->post('goods_id', 0);
        $data = Goods::CartCount($goods_id);
        if ($data && isset($data['code'])) {
            if ($data['code'] == 0) {
                return ['code' => 0, 'msg' => 'add cart count successful'];
            } else {
                return ['code' => $data['code'], 'msg' => $data['message']];
            }
        }
        return ['code' => 1, 'msg' => 'unknown error'];
    }


     public function actionListNew()
    {
         
        $r_id        = intval(YII::$app->request->get('r_id', 0));
        $category_id = intval(YII::$app->request->get('category_id', 0));
        $page        = YII::$app->request->get('page', 1);
        $per_page    = YII::$app->request->get('per_page', Goods::PER_PAGE);
        $type        = YII::$app->request->get('type', 'list_by_category');
        $gg_keywords = YII::$app->request->get('keyword', '');//from GOOGLE KEYWORD
        $keywords    = YII::$app->request->get('search_keyword', '');//from PC SEARCH
        $keywords    = $gg_keywords ? $gg_keywords : $keywords;

        $ads_keywords = YII::$app->request->get('ads_keyword', '');
        $order        = YII::$app->request->get('order', 'sell');
        $selectcolor  = YII::$app->request->get('selectcolor', '');
        $color        = $selectcolor ? explode('-', $selectcolor) : [];
        $selectshape  = YII::$app->request->get('selectshape', '');
        $shape        = $selectshape ? explode('-', $selectshape) : [];
        $selectlength  = YII::$app->request->get('length', '');
        $langth        = $selectlength ? explode('-', $selectlength) : [];
        $selectfeatrue  = YII::$app->request->get('featrue', '');
        $featrue        = $selectfeatrue ? explode('-', $selectfeatrue) : [];
        $selectfabric  = YII::$app->request->get('fabric', '');
        $fabric        = $selectfabric ? explode('-', $selectfabric) : [];
        $selectneckline  = YII::$app->request->get('neckline', '');
        $neckline        = $selectneckline ? explode('-', $selectneckline) : [];

        $spec        = array_merge($shape, $color,$langth,$featrue,$fabric,$neckline);
        $spec        = implode('-', $spec);

        $price_range = YII::$app->request->get('price_range', '');

        $price_range = $price_range_filter = $this->_check_price_range($price_range);
          
        //FROM GG ADS <<<
        $utm_source   = YII::$app->request->get('utm_source', '');//GG ; FB
        $utm_campaign = YII::$app->request->get('utm_campaign', '');//'white_t-shirt'
        $ads_keywords = $utm_campaign ? $utm_campaign : $ads_keywords;
        $utm_content  = YII::$app->request->get('utm_content', '');//1,2,3
        //>>>

        if ($utm_source) $type = 'list_by_gg';
        if ($keywords) $type = 'list_by_search';
        if ($ads_keywords) $type = 'list_by_keyword';
        if ($r_id) $type = 'list_by_recommend';

        //Sale分类映射
        $sale = 0;
        if(isset(\yii::$app->params['sale'][$category_id])){
            //$category_id = yii::$app->params['sale'][$category_id];
            $sale = 1;
        }

        $cate_name = 'All';

        switch ($type) {
            case 'list_by_category'://按分类
                break;
            case 'list_by_search'://按搜索
                $cate_name = $keywords;
                break;
            case 'list_by_keyword'://按广告关键词
                $cate_name = $ads_keywords;
                break;
            case 'list_by_gg'://按广告关键词
                //$cate_name = $ads_keywords;
                break;
            case 'list_by_recommend'://按推荐
                break;
        }

        $params['uid']          = isset($this->user_info['id']) ? $this->user_info['id'] : 0;
        $params['category_id']  = $category_id;
        $params['type']         = $type;
        $params['keywords']     = $keywords;
        $params['ads_keywords'] = $ads_keywords;
        $params['utm_content']  = $utm_content ? implode(",", array_reverse(explode(",", $utm_content))) : "";
        $params['utm_source']   = $utm_source;
        $params['spec']         = $spec;
        $params['price_range']  = $price_range;
        $params['order']        = $order;
        $params['page']         = $page;
        $params['per_page']     = $per_page;
        $params['shop_id']      = SHOP_ID;
        $params['lang']         = LANG_SET;
        $params['sale']         = $sale;
        $list                   = Goods::GoodsList($params);

        if ($list['cate_name']) $cate_name = $list['cate_name'];
        $pages = [];
        if (isset($list['total_num'])) {
            $pages = new Pagination(['totalCount' => $list['total_num'], 'pageSize' => $per_page]);
        }

        if (isset($list['category_info'])) {
            $this->view->params['meta_title']  = isset($list['category_info']['meta_title']) ? $list['category_info']['meta_title'] : '';
            $this->view->params['description'] = isset($list['category_info']['description']) ? $list['category_info']['description'] : '';
            $this->view->params['keyword']     = isset($list['category_info']['keyword']) ? $list['category_info']['keyword'] : '';
        }

        if ($price_range) {//SHOP BY PRICE SEO
            $price_range = explode('_', $price_range);
            $money       = end($price_range);
            if ($money == 10000) {
                $money = "up" . $price_range[0];
            }
            $this->view->params['meta_title']  = \Yii::t('shop', 'category_' . $category_id . '_price_title_' . $money);
            $this->view->params['description'] = \Yii::t('shop', 'category_' . $category_id . '_price_description_' . $money);
            $this->view->params['keyword']     = \Yii::t('shop', 'category_' . $category_id . '_price_keywords_' . $money);
        }

        $list_filter  = Goods::ListFilter($category_id,3,$spec,$price_range_filter);
        $all_category = Goods::Categories(SHOP_ID);
        $show_color   = Goods::ShowColor();

        $spec = $spec ? explode('-', $spec) : [];

        $cates         = $category_ids = [];
        $category_name = '';
        $parent_id     = -1;

        if ($list['category_info']) {
            $category_ids  = isset($list['category_info']['assoc_category_ids']) ? $list['category_info']['assoc_category_ids'] : [];
            $parent_id     = isset($list['category_info']['parent_id']) ? $list['category_info']['parent_id'] : [];
            $father_cat_id = 0;
            if ($category_ids) {
                $pos           = array_search(min($category_ids), $category_ids);
                $father_cat_id = $category_ids[$pos];
            }

            foreach ($all_category as $_category) {
                if ($_category['id'] == $father_cat_id) {
                    $cates         = $_category['cat_id'];
                    $category_name = $_category['name'];
                    break;
                }
            }
                         
            $urltitle = preg_replace('/[^a-z0-9\s]/','',strtolower($category_name));
            $urltitle = preg_replace('/\s+/','-',$urltitle);

            //跳到手机版
            if((new Mobile_Detect())->isMobile()){
                $Murl = yii::$app->params['MY_URL']['M']."/".strtolower($urltitle)."/".$category_id;
                if(\yii::$app->request->queryString){
                    $Murl .= "?".\yii::$app->request->queryString;
                }
                $this->redirect($Murl);
            }


            $this->view->params['bread'][] = [
                'url'  => "/".$urltitle ."-c" . $father_cat_id,
                'name' => $category_name
            ];

            if ($list['category_info']['parent_id'] > 0) {
                $c_name = isset($list['category_info']['cate_name']) ? $list['category_info']['cate_name'] : $list['category_info']['name'];
                $urltitle = preg_replace('/[^a-z0-9\s]/','',strtolower($c_name));
                $urltitle = preg_replace('/\s+/','-',$urltitle);
                $this->view->params['bread'][] = [
                    'url'  => "/".$urltitle ."-c" . $list['category_info']['id'],
                    'name' => isset($list['category_info']['cate_name']) ? $list['category_info']['cate_name'] : $list['category_info']['name']
                ];
            }
        }
        if ($type == 'list_by_search') {
            $this->view->params['bread'] = [
                [
                    'url'  => '',
                    'name' => $keywords
                ]
            ];
        }


        $urltitle = \yii::$app->params['MY_URL']['BS']."/".strtolower($cate_name)."-c".$category_id;
        if(\yii::$app->request->queryString){
            $urltitle .= "?".\yii::$app->request->queryString;
        }
        $urltitle2 = $this->view->params['bs']."/".strtolower($cate_name)."-c".$category_id;;
        if(\yii::$app->request->queryString){
            $urltitle2 .= "?".\yii::$app->request->queryString;
        }
        $canonical =  '<link rel="canonical" href="'.$urltitle2.'">';
        $this->view->params['canonical'] = $canonical;

        //wdx 0517
        $show_link = 1;
        $three_tkd = 1;
        $spec_str = 'price_range,neckline,fabric,featrue,selectshape,length,selectcolor';
        $garr = YII::$app->request->get();
        if(isset($parent_id)){
            $garr['parent_id'] = $parent_id;
        }
        if($garr){
            foreach ($garr as $key => $value) {
                if($key != 'category' &&  $key != 'category_id' ){
                    if(!empty($value) && strpos($spec_str,$key) !==false){ 
                          $show_link = 0;
                    }
                 }
            }
            if($show_link != 1 && isset($garr['category_id']) && isset($garr['parent_id'])){
                if($garr['category_id'] != $garr['parent_id']){
                    $three_tkd = 0;
                }
            }
        }
        
         foreach ($list['data'] as &$v){
                if(isset($v['covers']) && is_array($v['covers'])){
                    foreach ($v['covers'] as $k => $val) {
                        if(isset($v['covers'][$k]['url']))
                        $v['covers'][$k]['url'] = myhelper::resize($v['covers'][$k]['url'],251,376);
                    }
                    
                }
            }
           
        //   $requesturi = $_SERVER['REQUEST_URI'];

        //   if($requesturi){
        //     $tempre = explode('?',$requesturi);
            
        //     if(isset($tempre[1]) && $tempre[1]){  
        //         $requesturi  = $requesturi.'&';
        //         $temp_reqarr = explode('&',$tempre[1]);
        //         $temp_reqstr = '';
        //         if($temp_reqarr){
        //             foreach ($temp_reqarr as $key => $value) {
        //                 if($value)
        //                 if(strpos($value,'page=') === false){
        //                     if($temp_reqstr)
        //                     $temp_reqstr .= $value.'&';
        //                     else
        //                     $temp_reqstr =  $value.'&';
        //                 }
        //             }
        //         }
        //         $requesturi = $tempre[0].'?'.$temp_reqstr; 
        //     }else{
        //         if(strpos($requesturi,'?') ===false)
        //         $requesturi = $requesturi.'?';
        //     }
            
        //   }else{
        //     $requesturi = '?';
        //   }

        // if($show_link != 1 || $three_tkd != 1){
        //     $this->_setTKD($garr,$spec_str,$list_filter['spec'],$three_tkd,$list['cate_name']);
        // } 
        $data = [];
        $data['data'] = $list;
        $data['code'] = 0;
         echo json_encode($data);exit;
        // return $this->render('/people/index.html', [
        //     'data'               => $list,
        //     'pages'              => $pages,
        //     'sort'               => $order,
        //     'cate_name'          => $cate_name,
        //     'keywords'           => $keywords,
        //     'filter'             => $list_filter,
        //     'spec'               => $spec,
        //     'type'               => $type,
        //     'cates'              => $cates,
        //     'category_name'      => $category_name,
        //     'parent_id'          => $parent_id,
        //     'assoc_category_ids' => $category_ids,
        //     'show_color'         => $show_color,
        //     'selected_price'     => $price_range_filter,

        //     'selectshape'       =>$shape,
        //     'selectlength'       =>$langth,
        //     'selectfeatrue'       =>$featrue,
        //     'selectfabric'       =>$fabric,
        //     'selectneckline'       =>$neckline,
        //     'selectcolor'       =>$color,
        //     'show_link'         =>$show_link,
        //     'reqstr'            =>$requesturi
        // ]);

    }





     //wdx 0709
    public function actionGetlocationinfobyip()
    {
        $ips  = \yii::$app->request->get('ip', '');
        $client = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote = @$_SERVER['REMOTE_ADDR'];
        $result = array('country' => '', 'city' => '');
        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = $remote;
        }
        // var_dump($ip);
        if($ips) $ip= $ips;
        $ip_data = @json_decode
        (file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
         
        var_dump( $ip_data);exit;
        
    }

}
