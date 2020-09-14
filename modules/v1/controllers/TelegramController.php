<?php

namespace app\modules\v1\controllers;

use app\core\helpers\ArrayHelper;
use app\core\services\TelegramService;
use app\core\traits\ServiceTrait;
use app\core\types\AuthClientType;
use app\core\types\RecordSource;
use app\core\types\TelegramKeyword;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\CallbackQuery;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;
use TelegramBot\Api\Types\Update;
use yiier\graylog\Log;
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

            $bot->command(ltrim(TelegramKeyword::REPORT, '/'), function (Message $message) use ($bot) {
                $keyboard = new ReplyKeyboardMarkup(
                    [[TelegramKeyword::TODAY, TelegramKeyword::YESTERDAY, TelegramKeyword::LAST_MONTH]],
                    true
                );
                /** @var BotApi $bot */
                $bot->sendMessage($message->getChat()->getId(), '请选择统计范围', null, false, null, $keyboard);
            });

            $bot->command(ltrim(TelegramKeyword::CMD, '/'), function (Message $message) use ($bot) {
                $keyboard = new ReplyKeyboardMarkup(
                    [[TelegramKeyword::REPORT]],
                    true
                );
                /** @var BotApi $bot */
                $bot->sendMessage($message->getChat()->getId(), '请选择指令', null, false, null, $keyboard);
            });

            $bot->command('ping', function (Message $message) use ($bot) {
                $keyboard = new ReplyKeyboardMarkup(
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

            $bot->on(function (Update $Update) use ($bot) {
                $message = $Update->getMessage();
                $user = $this->userService->getUserByClientId(
                    AuthClientType::TELEGRAM,
                    $message->getFrom()->getId()
                );
                if ($user) {
                    \Yii::$app->user->setIdentity($user);
                    $type = ltrim($message->getText(), '/');
                    $text = $this->telegramService->getReportTextByType($type);
                } else {
                    $text = '请先绑定您的账号';
                }
                /** @var BotApi $bot */
                $bot->sendMessage($message->getChat()->getId(), $text);
            }, function (Update $message) {
                $msg = $message->getMessage();
                $report = [TelegramKeyword::TODAY, TelegramKeyword::YESTERDAY, TelegramKeyword::LAST_MONTH];
                if ($msg && in_array($msg->getText(), $report)) {
                    return true;
                }
                return false;
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
                    $text = $this->telegramService->getMessageTextByTransaction($model);
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
