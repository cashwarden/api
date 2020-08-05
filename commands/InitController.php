<?php

namespace app\commands;

use app\core\services\TelegramService;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Url;

class InitController extends Controller
{
    public function actionTelegram()
    {
        $url = Url::to('/v1/telegram/hook', true);
        TelegramService::newClient()->setWebHook($url);
        $this->stdout("Telegram set Webhook url success!: {$url}\n");
        return ExitCode::OK;
    }
}
