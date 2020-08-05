<?php

namespace app\core\services;

use TelegramBot\Api\Client;
use Yii;
use yii\base\InvalidConfigException;

class TelegramService
{
    /**
     * @return Client|object
     */
    public static function newClient()
    {
        try {
            return Yii::createObject(Client::class, [params('telegramToken')]);
        } catch (InvalidConfigException $e) {
            return new Client(params('telegramToken'));
        }
    }
}
