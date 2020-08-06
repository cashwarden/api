<?php

namespace app\modules\v1\controllers;

use app\core\services\TelegramService;
use app\core\traits\ServiceTrait;
use TelegramBot\Api\BotApi;

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
            $bot->command('devanswer', function ($message) use ($bot) {
                /** @var BotApi $bot */
                preg_match_all(
                    '/{"text":"(.*?)",/s',
                    file_get_contents('http://devanswers.ru/'),
                    $result
                );
                $bot->sendMessage(
                    $message->getChat()->getId(),
                    str_replace("<br/>", "\n", json_decode('"' . $result[1][0] . '"'))
                );
            });

            $bot->command('qaanswer', function ($message) use ($bot) {
                /** @var BotApi $bot */
                $bot->sendMessage($message->getChat()->getId(), file_get_contents('http://qaanswers.ru/qwe.php'));
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
