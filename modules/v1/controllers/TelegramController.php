<?php

namespace app\modules\v1\controllers;

use app\core\helpers\ArrayHelper;
use app\core\services\TelegramService;
use app\core\traits\ServiceTrait;
use app\core\types\AuthClientType;
use app\core\types\RecordSource;
use app\core\types\TelegramKeyword;
use app\core\types\TransactionType;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\CallbackQuery;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\Update;
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
                $user = $this->userService->getUserByClientId(
                    AuthClientType::TELEGRAM,
                    $message->getFrom()->getId()
                );
                if ($user) {
                    \Yii::$app->user->setIdentity($user);
                    $this->telegramService->callbackQuery($message, $bot);
                }
            });

            $bot->command('ping', function (Message $message) use ($bot) {
                $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup(
                    [["one", "two", "three"]],
                    true
                ); // true for one-time keyboard
                /** @var BotApi $bot */
                $bot->sendMessage($message->getChat()->getId(), 'pong!', null, false, null, $keyboard);
            });

            $bot->command(ltrim(TelegramKeyword::START, '/'), function (Message $message) use ($bot) {
                $text = "您还未绑定账号，请先访问「个人设置」中的「账号绑定」进行绑定账号，然后才能快速记账。";
                $user = $this->userService->getUserByClientId(
                    AuthClientType::TELEGRAM,
                    $message->getFrom()->getId()
                );
                if ($user) {
                    $text = '欢迎回来👏';
                }
                /** @var BotApi $bot */
                $bot->sendMessage($message->getChat()->getId(), $text);
            });

//            $bot->on(function (Update $Update) use ($bot) {
//                $message = $Update->getMessage();
//                /** @var BotApi $bot */
//                $bot->sendMessage($message->getChat()->getId(), "hi");
//            }, function (Update $message) {
//                if ($message->getMessage() && $message->getMessage()->getText() == '/login') {
//                    return true;
//                }
//                return false;
//            });

            $bot->on(function (Update $Update) use ($bot) {
                $message = $Update->getMessage();
                $token = StringHelper::after(TelegramKeyword::BIND . '/', $message->getText());
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
                $msg = $message->getMessage();
                if ($msg && strpos($msg->getText(), TelegramKeyword::BIND) === 0) {
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
                    $model = $this->transactionService->createByDesc($message->getText(), RecordSource::TELEGRAM);
                    $keyboard = $this->telegramService->getRecordMarkup($model);
                    $text = "记账成功😄" . "\n";
                    $text .= '交易类目： #' . $model->category->name . "\n";
                    $text .= '交易类型： #' . TransactionType::texts()[$model->type] . "\n";
                    $text .= "交易时间： {$model->date}\n"; // todo add tag
                    if (in_array($model->type, [TransactionType::EXPENSE, TransactionType::TRANSFER])) {
                        $fromAccountName = $model->fromAccount->name;
                        $fromAccountBalance = Setup::toYuan($model->fromAccount->balance_cent);
                        $text .= "支付账户： #{$fromAccountName} （余额：{$fromAccountBalance}）\n";
                    }
                    if (in_array($model->type, [TransactionType::INCOME, TransactionType::TRANSFER])) {
                        $toAccountName = $model->toAccount->name;
                        $toAccountBalance = Setup::toYuan($model->toAccount->balance_cent);
                        $text .= "收款账户： #{$toAccountName} （余额：{$toAccountBalance}）\n";
                    }
                    $text .= '金额：' . Setup::toYuan($model->amount_cent);
                } catch (\Exception $e) {
                    $text = $e->getMessage();
                }
                /** @var BotApi $bot */
                $bot->sendMessage($message->getChat()->getId(), $text, null, false, null, $keyboard);
            }, function (Update $message) {
                if ($message->getMessage()) {
                    if (ArrayHelper::strPosArr($message->getMessage()->getText(), TelegramKeyword::items()) === 0) {
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
}
