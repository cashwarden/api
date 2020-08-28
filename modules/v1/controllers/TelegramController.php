<?php

namespace app\modules\v1\controllers;

use app\core\services\TelegramService;
use app\core\traits\ServiceTrait;
use app\core\types\AuthClientType;
use app\core\types\TelegramAction;
use app\core\types\TransactionRating;
use app\core\types\TransactionType;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\CallbackQuery;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\Update;
use yii\helpers\Json;
use yiier\graylog\Log;
use yiier\helpers\Setup;
use yiier\helpers\StringHelper;

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

            $bot->callbackQuery(function (CallbackQuery $message) use ($bot) {
                $bot->answerCallbackQuery($message->getId(), "Loading...");
                $this->telegramService->callbackQuery($message, $bot);
            });

            $bot->command('ping', function (Message $message) use ($bot) {
                $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup(
                    [["one", "two", "three"]],
                    true
                ); // true for one-time keyboard
                /** @var BotApi $bot */
                $bot->sendMessage($message->getChat()->getId(), 'pong!', null, false, null, $keyboard);
            });

            $bot->on(function (Update $Update) use ($bot) {
                $message = $Update->getMessage();
                /** @var BotApi $bot */
                $bot->sendMessage($message->getChat()->getId(), "hi");
            }, function (Update $message) {
                if ($message->getMessage() && $message->getMessage()->getText() == '/login') {
                    return true;
                }
                return false;
            });

            $bot->on(function (Update $Update) use ($bot) {
                $message = $Update->getMessage();
                $token = StringHelper::after('/bind/', $message->getText());
                try {
                    $user = $this->userService->getUserByResetToken($token);
                    $this->telegramService->bind($user, $token, $message);
                    $text = '成功绑定账号【' . data_get($user, 'username') . '】！';
                } catch (\Exception $e) {
                    $text = $e->getMessage();
                }
                /** @var BotApi $bot */
                $bot->sendMessage($message->getChat()->getId(), $text);
            }, function (Update $message) {
                if ($message->getMessage() && strpos($message->getMessage()->getText(), '/bind') === 0) {
                    return true;
                }
                return false;
            });

            $bot->on(function (Update $Update) use ($bot) {
                $message = $Update->getMessage();
                $keyboard = null;
                try {
                    $user = $this->userService->getUserByClientId(
                        AuthClientType::TELEGRAM,
                        $message->getFrom()->getId()
                    );
                    \Yii::$app->user->setIdentity($user);
                    $model = $this->transactionService->createByDesc($message->getText());
                    $keyboard = new InlineKeyboardMarkup(
                        [
                            [
                                [
                                    'text' => '🚮删除',
                                    'callback_data' => Json::encode([
                                        'action' => TelegramAction::RECORD_DELETE,
                                        'id' => $model->id
                                    ]),
                                ],
                                [
                                    'text' => '😍' . TransactionRating::texts()[TransactionRating::MUST],
                                    'callback_data' => Json::encode([
                                        'action' => TelegramAction::TRANSACTION_RATING,
                                        'id' => $model->id,
                                        'value' => TransactionRating::MUST
                                    ]),
                                ],
                                [
                                    'text' => '😐' . TransactionRating::texts()[TransactionRating::NEED],
                                    'callback_data' => Json::encode([
                                        'action' => TelegramAction::TRANSACTION_RATING,
                                        'id' => $model->id,
                                        'value' => TransactionRating::NEED
                                    ]),
                                ],
                                [
                                    'text' => '💩' . TransactionRating::texts()[TransactionRating::WANT],
                                    'callback_data' => Json::encode([
                                        'action' => TelegramAction::TRANSACTION_RATING,
                                        'id' => $model->id,
                                        'value' => TransactionRating::WANT
                                    ]),
                                ]
                            ]
                        ]
                    );
                    $text = "记账成功\n";
                    $text .= '交易类型：' . TransactionType::getName($model->type) . "\n";
                    if (in_array($model->type, [TransactionType::EXPENSES, TransactionType::TRANSFER])) {
                        $fromAccountName = $model->fromAccount->name;
                        $fromAccountBalance = Setup::toYuan($model->fromAccount->balance_cent);
                        $text .= "支付账户： {$fromAccountName} （余额：{$fromAccountBalance}）\n";
                    }
                    if (in_array($model->type, [TransactionType::INCOME, TransactionType::TRANSFER])) {
                        $toAccountName = $model->toAccount->name;
                        $toAccountBalance = Setup::toYuan($model->toAccount->balance_cent);
                        $text .= "收款账户： {$toAccountName} （余额：{$toAccountBalance}）\n";
                    }
                    $text .= '金额：' . Setup::toYuan($model->amount_cent);
                } catch (\Exception $e) {
                    $text = $e->getMessage();
                }
                /** @var BotApi $bot */
                $bot->sendMessage($message->getChat()->getId(), $text, null, false, null, $keyboard);
            }, function (Update $message) {
                if ($message->getMessage()) {
                    if (strpos($message->getMessage()->getText(), '/bind') === 0) {
                        return false;
                    }
                    return true;
                }
                return false;
            });

            $bot->run();
        } catch (\TelegramBot\Api\Exception $e) {
            Log::error('webHook error' . $e->getMessage(), (string)$e);
            throw $e;
        }
        return '';
    }

    public function actionBind()
    {
        dd(\Yii::$app->request->queryParams);
    }
}
