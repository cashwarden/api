<?php

use yii\log\Logger;

return [
    'timeZone' => env('APP_TIME_ZONE'),
    'language' => env('APP_LANGUAGE'),
    'name' => env('APP_NAME'),
    'bootstrap' => ['log', 'ideHelper', \app\core\EventBootstrap::class],
    'components' => [
        'ideHelper' => [
            'class' => 'Mis\IdeHelper\IdeHelper',
            'configFiles' => [
                'config/web.php',
                'config/common.php',
                'config/console.php',
            ],
        ],
        'telegram' => [
            'class' => 'aki\telegram\Telegram',
            'botToken' => env('TELEGRAM_TOKEN'),
        ],
        'requestId' => [
            'class' => \yiier\helpers\RequestId::class,
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'formatter' => [
            'dateFormat' => 'yyyy-MM-dd',
            'datetimeFormat' => 'yyyy-MM-dd HH:mm:ss',
            'decimalSeparator' => ',',
            'thousandSeparator' => ' ',
            'currencyCode' => 'CNY',
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => env('DB_DSN'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'tablePrefix' => env('DB_TABLE_PREFIX'),
            'charset' => 'utf8mb4',
            'enableSchemaCache' => YII_ENV_PROD,
            'schemaCacheDuration' => 60,
            'schemaCache' => 'cache',
        ],
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/core/messages',
                    'fileMap' => [
                        'app' => 'app.php',
                        'app/error' => 'exception.php',
                    ],
                ],
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => yiier\graylog\Target::class,
                    // 日志等级
                    'levels' => Logger::LEVEL_ERROR | Logger::LEVEL_WARNING,
                    'logVars' => ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION'],
                    'categories' => [
                        'yii\db\*',
                        'yii\web\HttpException:*',
                        'application',
                    ],
                    'except' => [
                        'yii\web\HttpException:404',
                    ],
                    'transport' => [
                        'class' => yiier\graylog\transport\UdpTransport::class,
                        'host' => getenv('GRAYLOG_HOST'),
                        'chunkSize' => 4321,
                    ],
                    'additionalFields' => [
                        'request_id' => function ($yii) {
                            return Yii::$app->requestId->id;
                        },
                        'user_ip' => function ($yii) {
                            return ($yii instanceof \yii\console\Application) ? '' : $yii->request->userIP;
                        },
                        'tag' => getenv('GRAYLOG_TAG')
                    ],
                ],
                [
                    'class' => yiier\graylog\Target::class,
                    'levels' => Logger::LEVEL_ERROR | Logger::LEVEL_WARNING | Logger::LEVEL_INFO,
                    'logVars' => ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION'],
                    'categories' => [
                        'graylog'
                    ],
                    'except' => [
                        'yii\web\HttpException:404',
                    ],
                    'transport' => [
                        'class' => yiier\graylog\transport\UdpTransport::class,
                        'host' => getenv('GRAYLOG_HOST'),
                        'chunkSize' => 4321,
                    ],
                    'additionalFields' => [
                        'request_id' => function ($yii) {
                            return Yii::$app->requestId->id;
                        },
                        'user_ip' => function ($yii) {
                            return ($yii instanceof \yii\console\Application) ? '' : $yii->request->userIP;
                        },
                        'tag' => getenv('GRAYLOG_TAG')
                    ],
                ],
                /**
                 * 错误级别日志：当某些需要立马解决的致命问题发生的时候，调用此方法记录相关信息。
                 * 使用方法：Yii::error()
                 */
                [
                    'class' => 'yiier\helpers\FileTarget',
                    // 日志等级
                    'levels' => ['error'],
                    'except' => [
                        'yii\web\HttpException:404',
                    ],
                    // 被收集记录的额外数据
                    'logVars' => ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION'],
                    // 指定日志保存的文件名
                    'logFile' => '@app/runtime/logs/error/app.log',
                    // 是否开启日志 (@app/runtime/logs/error/20151223_app.log)
                    'enableDatePrefix' => true,
                ],
                /**
                 * 警告级别日志：当某些期望之外的事情发生的时候，使用该方法。
                 * 使用方法：Yii::warning()
                 */
                [
                    'class' => 'yiier\helpers\FileTarget',
                    // 日志等级
                    'levels' => ['warning'],
                    // 被收集记录的额外数据
                    'logVars' => ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION'],
                    // 指定日志保存的文件名
                    'logFile' => '@app/runtime/logs/warning/app.log',
                    // 是否开启日志 (@app/runtime/logs/warning/20151223_app.log)
                    'enableDatePrefix' => true,
                ],
                [
                    'class' => 'yiier\helpers\FileTarget',
                    'levels' => ['info'],
                    'categories' => ['request'],
                    'logVars' => [],
                    'maxFileSize' => 1024,
                    'logFile' => '@app/runtime/logs/request/app.log',
                    'enableDatePrefix' => true
                ],
                [
                    'class' => 'yiier\helpers\FileTarget',
                    'levels' => ['warning'],
                    'categories' => ['debug'],
                    'logVars' => [],
                    'maxFileSize' => 1024,
                    'logFile' => '@app/runtime/logs/debug/app.log',
                    'enableDatePrefix' => true
                ],
            ],
        ],
    ],
];
