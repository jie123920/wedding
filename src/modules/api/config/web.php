<?php
if (YII_ENV == 'dev') {
    define('BLOCK_1', 16);
    define('BLOCK_2', 15);
    define('BLOCK_3', 14);
    define('BLOCK_4', 13);
    define('BLOCK_5', 12);
    define('BLOCK_6', 11);
    define('BLOCK_7', 10);
    define('SHOP_ID', 3);
    define('MAX_CART_COUNT_ITEM_NUMBER', 50);
    define('MAX_CART_ITEM_NUMBER', 30);
    define('CUSTOM_ID', '190,578,631,739');//定制ID
} elseif (YII_ENV == 'qa') {
    define('BLOCK_1', 16);
    define('BLOCK_2', 15);
    define('BLOCK_3', 14);
    define('BLOCK_4', 13);
    define('BLOCK_5', 12);
    define('BLOCK_6', 11);
    define('BLOCK_7', 10);
    define('SHOP_ID', 3);
    define('MAX_CART_COUNT_ITEM_NUMBER', 50);
    define('MAX_CART_ITEM_NUMBER', 30);
    define('CUSTOM_ID', '190,578,631,739');//定制ID
} else {
    define('BLOCK_1', 16);
    define('BLOCK_2', 15);
    define('BLOCK_3', 14);
    define('BLOCK_4', 13);
    define('BLOCK_5', 12);
    define('BLOCK_6', 11);
    define('BLOCK_7', 10);
    define('SHOP_ID', 3);
    define('MAX_CART_COUNT_ITEM_NUMBER', 50);
    define('MAX_CART_ITEM_NUMBER', 30);
    define('CUSTOM_ID', '190,578,631,739');//定制ID
}
define('SHOP_API_URL',\YII::$app->params['MY_URL']['LC']."/shop/");

$_config = [
    'id'                  => 'api',
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
