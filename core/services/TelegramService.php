<?php

namespace app\core\services;

use app\core\models\AuthClient;
use app\core\models\Transaction;
use app\core\models\User;
use app\core\traits\ServiceTrait;
use app\core\types\AuthClientStatus;
use app\core\types\AuthClientType;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\CallbackQuery;
use TelegramBot\Api\Types\Message;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\db\Exception as DBException;
use yii\helpers\Json;
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
     * @param User $user
     * @param string $token
     * @param Message $message
     * @throws DBException
     */
    public function bind(User $user, string $token, Message $message): void
    {
        Yii::error($message, 'telegram_message' . $token);

        $conditions = [
            'type' => AuthClientType::TELEGRAM,
            'user_id' => $user->id,
            'status' => AuthClientStatus::ACTIVE
        ];
        if (!$model = AuthClient::find()->where($conditions)->one()) {
            $model = new AuthClient();
            $model->load($conditions, '');
        }
        $model->client_username = (string)$message->getFrom()->getUsername();
        $model->client_id = (string)$message->getFrom()->getId();
        $model->data = $message->getFrom()->toJson();
        if (!$model->save()) {
            throw new DBException(Setup::errorMessage($model->firstErrors));
        }
    }

    /**
     * @param CallbackQuery $message
     * @param Client $bot
     * @throws \Throwable
     */
    public function callbackQuery(CallbackQuery $message, Client $bot)
    {
        $data = Json::decode($message->getData());
        if (data_get($data, 'action') == 'delete') {
            $text = '记录删除失败';
            try {
                if ($model = Transaction::find()->where(data_get($data, 'id'))->one()->delete()) {
                    $text = '记录成功被删除';
                }
            } catch (\Exception $e) {
                $text = '记录删除失败: ' . $e->getMessage();
            }
            /** @var BotApi $bot */
            $bot->sendMessage($message->getFrom()->getId(), $text);
        }
    }
}
