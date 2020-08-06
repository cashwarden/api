<?php

namespace app\modules\v1\controllers;

use app\core\traits\ServiceTrait;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;

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
            # $bot = TelegramService::newClient();
            $bot = new \TelegramBot\Api\Client(params('telegramToken'));
//            $bot->command('ping', function (Message $message) use ($bot) {
//                /** @var BotApi $bot */
//                $bot->sendMessage($message->getChat()->getId(), 'pong!');
//            });

            $bot->on(function (Update $Update) use ($bot) {
                $message = $Update->getMessage();
                /** @var BotApi $bot */
                $bot->sendMessage($message->getChat()->getId(), "hi");
            }, function (Update $message) use ($bot) {
                if ($message->getMessage() == '/login') {
                    return true;
                }
                return false;
            });

            $bot->run();
        } catch (\TelegramBot\Api\Exception $e) {
            dump($e->getMessage());
            throw $e;
        }
    }

    public function actionBind()
    {
        dd(\Yii::$app->request->queryParams);
    }
}
