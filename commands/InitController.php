<?php

namespace app\commands;

use app\core\models\User;
use app\core\services\TelegramService;
use app\core\traits\ServiceTrait;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Url;

class InitController extends Controller
{
    use ServiceTrait;

    public function actionTelegram()
    {
        $url = Url::to('/telegram/hook', true);
        TelegramService::newClient()->setWebHook($url);
        $this->stdout("Telegram set Webhook url success!: {$url}\n");
        return ExitCode::OK;
    }

    /**
     * @param int $userId
     * @throws \app\core\exceptions\InvalidArgumentException
     * @throws \yii\db\Exception
     */
    public function actionUserData(int $userId)
    {
        $this->userService->createUserAfterInitData(User::findOne($userId));
        $this->stdout("User Account and Category init success! \n");
    }
}
