<?php

namespace app\modules\v1\controllers;

use app\core\services\TelegramService;
use app\core\traits\ServiceTrait;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;
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
                /** @var BotApi $bot */
                $bot->sendMessage($message->getChat()->getId(), 'pong!');
            });
            $bot->command('login', function ($message) use ($bot) {
                /** @var BotApi $bot */
                $bot->sendMessage($message->getChat()->getId(), Url::to('/v1/telegram/bind', true));
            });

            // react only on self contact
            $bot->on(
                function (Update $update) use ($bot) {
                    /** @var BotApi $bot */
                    $bot->sendMessage(
                        $update->getMessage()->getChat()->getId(),
                        $update->getMessage()->getContact()->getPhoneNumber()
                    );
                },
                function (Update $update) {
                    return $update->getMessage()
                        && $update->getMessage()->getContact()
                        &&
                        $update->getMessage()->getContact()->getUserId() === $update->getMessage()->getFrom()->getId();
                }
            );
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
