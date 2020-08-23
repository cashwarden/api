<?php

namespace app\modules\v1\controllers;

use app\core\services\TelegramService;
use app\core\traits\ServiceTrait;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\Update;
use yiier\graylog\Log;
use yiier\helpers\StringHelper;

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
            $bot->command('ping', function (Message $message) use ($bot) {
                /** @var BotApi $bot */
                $bot->sendMessage($message->getChat()->getId(), 'pong!');
            });

            $bot->on(function (Update $Update) use ($bot) {
                $message = $Update->getMessage();
                /** @var BotApi $bot */
                $bot->sendMessage($message->getChat()->getId(), "hi");
            }, function (Update $message) {
                if ($message->getMessage() && $message->getMessage()->getText() == '/login') {
                    return true;
                }
                return false;
            });

            $bot->on(function (Update $Update) use ($bot) {
                $message = $Update->getMessage();
                $token = StringHelper::after('/bing/', $message->getText());
                $this->userService->bingTelegram($token, $message);
                /** @var BotApi $bot */
                $bot->sendMessage($message->getChat()->getId(), "绑定成功！");
            }, function (Update $message) {
                if ($message->getMessage() && strpos($message->getMessage()->getText(), '/bind') === 0) {
                    return true;
                }
                return false;
            });

            $bot->on(function (Update $Update) use ($bot) {
                $message = $Update->getMessage();
                /** @var BotApi $bot */
                $bot->sendMessage($message->getChat()->getId(), $message->getText());
            }, function (Update $message) {
                return true;
            });

            $bot->run();
        } catch (\TelegramBot\Api\Exception $e) {
            Log::error('webHook error' . $e->getMessage(), (string)$e);
            throw $e;
        }
        return '';
    }

    public function actionBind()
    {
        dd(\Yii::$app->request->queryParams);
    }
}
