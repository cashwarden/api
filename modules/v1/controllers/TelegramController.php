<?php

namespace app\modules\v1\controllers;

use app\core\services\TelegramService;
use app\core\traits\ServiceTrait;

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
            $bot->on(function ($update) use ($bot) {
                $message = $update->getMessage();
                $input = $message->getText();
                $cid = $message->getChat()->getId();
                if ($input === "/start") {
                    $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup([
                        [
                            ["text" => "кнопка",],
                            ["text" => "кнопка"]
                        ]
                    ], true, true);
                    $bot->sendMessage($cid, 'старт!', null, true, null, $keyboard);
                    $bot->answerCallbackQuery($update->getCallbackQuery()->getId());
                }
            }, function ($update) use ($bot) {
                $msg = $update->getMessage();
                if (is_null($msg) || !strlen($msg->getText())) {
                    return false;
                }
                return true;
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
