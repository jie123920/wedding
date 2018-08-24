<?php
namespace app\modules\wedding\services;

/**
 * 定制尺码表说明
 * @package app\modules\wedding\services
 */
class CustomSize extends Service
{

    //cm  inch 两种单位
    //inch 每隔0.5一单位
    //cm    每隔1一单位
    public static $CUSTOM_SIZE = [
        'cm'=>[
            'Bust'=>[
                'start'=>'53',//最小值
                'end'=>'160'//最大值
            ],
            'Waist'=>[
                'start'=>'51',
                'end'=>'160'
            ],
            'Hips'=>[
                'start'=>'51',
                'end'=>'160'
            ],
            'Hollow to Floor'=>[
                'start'=>'55',
                'end'=>'190'
            ]
        ],
        'inch'=>[
            'Bust'=>[
                'start'=>'21',
                'end'=>'63'
            ],
            'Waist'=>[
                'start'=>'20',
                'end'=>'63'
            ],
            'Hips'=>[
                'start'=>'20',
                'end'=>'63'
            ],
            'Hollow to Floor'=>[
                'start'=>'22',
                'end'=>'75'
            ]
        ]
    ];

}