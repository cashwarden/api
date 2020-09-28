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
            'hostInfo' => getenv('APP_URL'),
            'rules' => [
                "POST <module>/<alias:login|join>" => '<module>/user/<alias>',
                "POST <module>/token/refresh" => '<module>/user/refresh-token',
                "POST <module>/transactions/by-description" => '<module>/transaction/create-by-description',
                "POST <module>/rules/<id:\d+>/copy" => '<module>/rule/copy',
                "PUT <module>/rules/<id:\d+>/status" => '<module>/rule/update-status',
                "GET <module>/accounts/types" => '<module>/account/types',
                "GET <module>/accounts/overview" => '<module>/account/overview',
                "POST <module>/reset-token" => '<module>/user/reset-token',
                "GET <module>/users/auth-clients" => '<module>/user/get-auth-clients',
                "GET <module>/transactions/<alias:types|export>" => '<module>/transaction/<alias>',
                "POST <module>/transactions/upload" => '<module>/transaction/upload',
                "GET <module>/records/overview" => '<module>/record/overview',
                "GET <module>/categories/analysis" => '<module>/category/analysis',
                "GET <module>/records/analysis" => '<module>/record/analysis',
                "GET <module>/records/sources" => '<module>/record/sources',
                "PUT <module>/recurrences/<id:\d+>/status" => '<module>/recurrence/update-status',
                "GET <module>/recurrences/frequencies" => '<module>/recurrence/frequency-types',

                "GET <module>/site-config" => '/site/data',
                "GET <module>/<alias:icons>" => '/site/<alias>',
                "GET health-check" => 'site/health-check',
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'v1/account',
                        'v1/category',
                        'v1/rule',
                        'v1/tag',
                        'v1/record',
                        'v1/transaction',
                        'v1/recurrence',
                    ]
                ],
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
