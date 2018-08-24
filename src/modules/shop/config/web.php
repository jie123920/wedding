<?php
define('__STATIC__',CDN_URL.'/static');
define('__JS__',CDN_URL.'/shop/js');
define('__IMG__',CDN_URL.'/shop/images');
define('__CSS__',CDN_URL.'/shop/css');
define('__AVATARS__',CDN_URL.'/Common/images/UserAvatar');
define('__LAYER__',CDN_URL.'/shop/layer');
define('SHOP_API_URL',\YII::$app->params['MY_URL']['LC']."/shop/");
if(YII_ENV == 'dev'){
    define('TID',33);
    define('EVENTS_ID',42);
    define('ANNOUNCEMENT_ID',43);
    define('GUIDE_ID',47);
    define('STRATEGY_ID',49);
    define('SHOP_ID', 3);
    define('MAX_CART_COUNT_ITEM_NUMBER', 50);
    define('MAX_CART_ITEM_NUMBER', 190);
}elseif(YII_ENV == 'qa'){
    define('TID',32);
    define('EVENTS_ID',34);
    define('ANNOUNCEMENT_ID',35);
    define('GUIDE_ID',36);
    define('STRATEGY_ID',41);
    define('SHOP_ID', 3);
    define('MAX_CART_COUNT_ITEM_NUMBER', 50);
    define('MAX_CART_ITEM_NUMBER', 30);
}else{
    define('TID',32);
    define('EVENTS_ID',34);
    define('ANNOUNCEMENT_ID',35);
    define('GUIDE_ID',36);
    define('STRATEGY_ID',41);
    define('SHOP_ID', 3);
    define('MAX_CART_COUNT_ITEM_NUMBER', 50);
    define('MAX_CART_ITEM_NUMBER', 30);
}
$_config = [
    'id'                  => 'shop',
    'language'=>'en-us',
    'components'          => [
        'i18n' => [
            'class'        => 'yii\i18n\I18N',
            'translations' => [
                '*' => [
                    'class'    => 'yii\i18n\PhpMessageSource',
                    'basePath' => dirname(__DIR__).'/languages/',
                    'forceTranslation'=>true
                ],
            ],
        ],
    ],
];

return $_config;
