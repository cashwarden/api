<?php

use app\core\components\ResponseHandler;
use app\core\models\User;

$common = require(__DIR__ . '/common.php');
$params = require __DIR__ . '/params.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'modules' => [
        'v1' => [
            'class' => 'app\modules\v1\Module',
        ],
    ],
    'components' => [
        'request' => [
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => env('COOKIE_VALIDATION_KEY')
        ],
        'response' => [
            'class' => 'yii\web\Response',
            'on beforeSend' => function ($event) {
                yii::createObject([
                    'class' => ResponseHandler::class,
                    'event' => $event,
                ])->formatResponse();
            },
        ],
        'jwt' => [
            'class' => \sizeg\jwt\Jwt::class,
            'key' => env('JWT_SECRET'),
        ],
        'formatter' => [
            'dateFormat' => 'yyyy-MM-dd',
            'datetimeFormat' => 'yyyy-MM-dd HH:mm:ss',
            'decimalSeparator' => ',',
            'thousandSeparator' => ' ',
            'currencyCode' => 'CNY',
        ],
        'user' => [
            'identityClass' => User::class,
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            // 'class' => '\yii\web\ErrorHandler',
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                "POST <module>/<alias:login|join>" => '<module>/user/<alias>',
                "POST <module>/token/refresh" => '<module>/user/refresh-token',
                "POST <module>/transactions/by-description" => '<module>/transaction/create-by-description',
                "POST <module>/rules/<id:\d+>/copy" => '<module>/rule/copy',
                "PUT <module>/rules/<id:\d+>/status" => '<module>/rule/update-status',
                "GET <module>/accounts/types" => '<module>/account/types',
                "GET <module>/accounts/statistics" => '<module>/account/statistics',
                "POST <module>/reset-token" => '<module>/user/reset-token',
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'v1/account',
                        'v1/category',
                        'v1/rule',
                        'v1/tag',
                        'v1/record',
                        'v1/transaction',
                    ]
                ],
                "GET health-check" => 'site/health-check',
                '<module>/<controller:\w+>/<action:\w+>/<id:\d+>' => '<module>/<controller>/<action>',
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['*'],
    ];
}

return \yii\helpers\ArrayHelper::merge($common, $config);
