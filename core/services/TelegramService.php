<?php

namespace app\core\services;

use app\core\models\AuthClient;
use app\core\models\Transaction;
use app\core\models\User;
use app\core\traits\ServiceTrait;
use app\core\types\AuthClientStatus;
use app\core\types\AuthClientType;
use app\core\types\TelegramAction;
use app\core\types\TransactionRating;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Client;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;
use TelegramBot\Api\Types\CallbackQuery;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
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
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws \Throwable
     */
    public function callbackQuery(CallbackQuery $message, Client $bot)
    {
        $data = Json::decode($message->getData());
        $text = '操作失败';
        switch (data_get($data, 'action')) {
            case TelegramAction::RECORD_DELETE:
                /** @var Transaction $model */
                if ($model = Transaction::find()->where(['id' => data_get($data, 'id')])->one()) {
                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        foreach ($model->records as $record) {
                            $record->delete();
                        }
                        $text = '记录成功被删除';
                        $transaction->commit();
                    } catch (\Exception $e) {
                        $transaction->rollBack();
                        $text = '记录删除失败: ' . $e->getMessage();
                    }
                } else {
                    $text = '删除失败，记录已被删除或者不存在';
                }
                $replyToMessageId = $message->getMessage()->getMessageId();
                /** @var BotApi $bot */
                $bot->sendMessage($message->getFrom()->getId(), $text, null, false, $replyToMessageId);
                break;
            case TelegramAction::TRANSACTION_RATING:
                $id = data_get($data, 'id');
                if ($this->transactionService->updateRating($id, data_get($data, 'value'))) {
                    $text = '评分成功';
                } else {
                    $text = '评分失败，记录已被删除或者不存在';
                }
                $replyMarkup = $this->getRecordMarkup(Transaction::findOne($id));
                /** @var BotApi $bot */
                $bot->sendMessage($message->getFrom()->getId(), $text, null, false, null, $replyMarkup);
                break;
            default:
                # code...
                break;
        }
    }

    public function getRecordMarkup(Transaction $model)
    {
        $tests = TransactionRating::texts();
        $rating = [];
        foreach (TransactionRating::names() as $key => $name) {
            $rating[TransactionRating::MUST] = null;
        }
        if ($model->rating) {
            $rating[$model->rating] = 1;
        }
        $items = [
            [
                'text' => '🚮删除',
                'callback_data' => Json::encode([
                    'action' => TelegramAction::RECORD_DELETE,
                    'id' => $model->id
                ]),
            ],
            [
                'text' => '😍' . $tests[TransactionRating::MUST] . $rating[TransactionRating::MUST],
                'callback_data' => Json::encode([
                    'action' => TelegramAction::TRANSACTION_RATING,
                    'id' => $model->id,
                    'value' => TransactionRating::MUST
                ]),
            ],
            [
                'text' => '😐' . $tests[TransactionRating::NEED] . $rating[TransactionRating::NEED],
                'callback_data' => Json::encode([
                    'action' => TelegramAction::TRANSACTION_RATING,
                    'id' => $model->id,
                    'value' => TransactionRating::NEED
                ]),
            ],
            [
                'text' => '💩' . $tests[TransactionRating::WANT] . $rating[TransactionRating::WANT],
                'callback_data' => Json::encode([
                    'action' => TelegramAction::TRANSACTION_RATING,
                    'id' => $model->id,
                    'value' => TransactionRating::WANT
                ]),
            ]
        ];

        return new InlineKeyboardMarkup([$items]);
    }
}
