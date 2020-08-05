<?php

namespace app\modules\v1\controllers;

use app\core\services\TelegramService;
use app\core\traits\ServiceTrait;
use yii\helpers\Url;

class TelegramController extends ActiveController
{
    use ServiceTrait;

    public $modelClass = '';
    public $noAuthActions = ['hook', 'bind'];

    public function actions()
    {
        $actions = parent::actions();
        // 注销系统自带的实现方法
        unset($actions['update'], $actions['index'], $actions['delete'], $actions['create']);
        return $actions;
    }

    /**
     * @throws \Exception
     */
    public function actionHook()
    {
        try {
            $bot = TelegramService::newClient();

            $bot->command('ping', function ($message) use ($bot) {
                $bot->sendMessage($message->getChat()->getId(), 'pong!');
            });
            $bot->command('login', function ($message) use ($bot) {
                $bot->sendMessage($message->getChat()->getId(), Url::to('/v1/telegram/bind', true));
            });
            $bot->run();
        } catch (\TelegramBot\Api\Exception $e) {
            dump($e->getMessage());
            throw $e;
        }
    }

    public function actionBind()
    {
        dd(\Yii::$app->request->params);
    }
}
