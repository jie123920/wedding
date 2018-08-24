<?php
define('__STATIC__', CDN_URL . '/static');
define('__JS__', CDN_URL . '/wedding/js');
define('__IMG__', CDN_URL . '/wedding/images');
define('__CSS__', CDN_URL . '/wedding/css');
define('__AVATARS__', CDN_URL . '/Common/images/UserAvatar');
define('FB_VERSION','v2.12');
define('FB_APPID','1884113755190368');
define('GG_APPID','409532259699-ue7jn8ov3f5sdrgl5v4uutb51povhiva.apps.googleusercontent.com');
define('SHOP_API_URL',\YII::$app->params['MY_URL']['LC']."/shop/");
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
    define('COLOR', '10,22');
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
    define('COLOR', '22,28,42,43,44');
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
    define('COLOR', '22,28,42,43,44,46,47');
}
$_config = [
    'id'         => 'wedding',
    'language'   => 'en-us',
    'components' => [
        'i18n' => [
            'class'        => 'yii\i18n\I18N',
            'translations' => [
                '*' => [
                    'class'            => 'yii\i18n\PhpMessageSource',
                    'basePath'         => dirname(__DIR__) . '/languages/src',
                    'forceTranslation' => true,
                ],
                'shop' => [
                    'class'            => 'yii\i18n\PhpMessageSource',
                    'basePath'         => dirname(dirname(__DIR__)) . '/shop/languages/',
                    'fileMap' => [
                        'shop' => 'common.php',
                    ],
                    'forceTranslation' => true,
                ],
                'cf' => [
                    'class'            => 'yii\i18n\PhpMessageSource',
                    'basePath'         => dirname(dirname(__DIR__)) . '/cf/languages/',
                    'fileMap' => [
                        'cf' => 'common.php',
                    ],
                    'forceTranslation' => true,
                ],
            ],
        ],
    ],
];

return $_config;
