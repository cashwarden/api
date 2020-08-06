<?php

namespace app\modules\v1\controllers;

use app\core\traits\ServiceTrait;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;
use yiier\graylog\Log;

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
        $path = "https://api.telegram.org/bot" . params('telegramToken');

        $update = json_decode(file_get_contents("php://input"), true);

        $chatId = $update["message"]["chat"]["id"];
        $message = $update["message"]["text"];

        if (strpos($message, "/weather") === 0) {
            $location = substr($message, 9);
            $weather = json_decode(
                file_get_contents("http://api.openweathermap.org/data/2.5/weather?q={$location}&appid=mytoken"),
                true
            )["weather"][0]["main"];
            file_get_contents(
                "{$path}/sendmessage?chat_id={$chatId}&text=Here's the weather in {$location}:{$weather}"
            );
        }
        die;


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
                Log::error('webHook info' . $message->getMessage()->getText());
                if ($message->getMessage()->getText() == '/login') {
                    return true;
                }
                return false;
            });

            $bot->run();
            \Yii::$app->response->send();
            die;
        } catch (\TelegramBot\Api\Exception $e) {
            Log::error('webHook error' . $e->getMessage(), (string)$e);
            throw $e;
        }
    }

    public function actionBind()
    {
        dd(\Yii::$app->request->queryParams);
    }
}
