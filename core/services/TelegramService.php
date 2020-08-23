<?php

namespace app\core\services;

use app\core\exceptions\InvalidArgumentException;
use app\core\models\AuthClient;
use app\core\traits\ServiceTrait;
use app\core\types\AuthClientStatus;
use app\core\types\AuthClientType;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\db\Exception as DBException;
use yiier\helpers\Setup;

class TelegramService extends BaseObject
{
    use ServiceTrait;

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

    /**
     * @param string $token
     * @param $message
     * @throws InvalidArgumentException|DBException
     * @throws \Exception
     */
    public function bind(string $token, Message $message)
    {
        Yii::error($message, 'telegram_message' . $token);

        $user = $this->userService->getUserByResetToken($token);
        $conditions = [
            'type' => AuthClientType::TELEGRAM,
            'user_id' => data_get($user, 'id'),
            'status' => AuthClientStatus::ACTIVE
        ];
        if (!$model = AuthClient::find()->where($conditions)->one()) {
            $model = new AuthClient();
            $model->load($conditions, '');
        }
        $model->client_id = (string)$message->getFrom()->getId();
        $model->data = json_encode($message);
        if (!$model->save()) {
            throw new DBException(Setup::errorMessage($model->firstErrors));
        }
    }
}
