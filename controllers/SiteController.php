<?php

declare(strict_types=1);

namespace app\controllers;

use Yii;
use yii\rest\Controller;

class SiteController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        return 'hello yii';
    }

    /**
     * @return string
     */
    public function actionHealthCheck()
    {
        return 'OK';
    }

    /**
     * @return array
     */
    public function actionError(): array
    {
        $exception = Yii::$app->errorHandler->exception;
        if ($exception !== null) {
            Yii::error([
                'request_id' => Yii::$app->requestId->id,
                'exception' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ], 'response_data_error');

            return ['code' => $exception->getCode(), 'message' => $exception->getMessage()];
        }
        return [];
    }

    public function actionData()
    {
        return [
            'app' => [
                'name' => Yii::$app->name,
                'description' => params('seoDescription'),
                'keywords' => params('seoKeywords'),
                'google_analytics' => params('googleAnalyticsAU')
            ],
            'menu' => [
                [
                    'text' => 'Main',
                    'group' => false,
                    'children' => [
                        [
                            'text' => '仪表盘',
                            'link' => '/dashboard',
                            'icon' => 'anticon-dashboard',
                        ],
                        [
                            'text' => '账号',
                            'link' => '/account/index',
                            'icon' => 'anticon-account-book',
                        ],
                        [
                            'text' => '记录',
                            'link' => '/record/index',
                            'icon' => 'anticon-database',
                        ],
                        [
                            'text' => '设置',
                            'icon' => 'anticon-setting',
                            'children' => [
                                [
                                    'text' => '个人设置',
                                    'link' => '/settings/personal',
                                    'icon' => 'anticon-user',
                                ],
                                [
                                    'text' => '分类设置',
                                    'link' => '/settings/categories',
                                    'icon' => 'anticon-appstore',
                                ],
                                [
                                    'text' => '标签设置',
                                    'link' => '/settings/tags',
                                    'icon' => 'anticon-appstore',
                                ],
                                [
                                    'text' => '规则设置',
                                    'link' => '/settings/rules',
                                    'icon' => 'anticon-group',
                                ]
                            ]
                        ],
                    ]
                ]
            ]
        ];
    }
}
