<?php

namespace app\commands;

use app\core\models\AuthClient;
use app\core\models\Recurrence;
use app\core\models\User;
use app\core\traits\ServiceTrait;
use app\core\types\AnalysisDateType;
use app\core\types\AuthClientType;
use app\core\types\RecurrenceStatus;
use Yii;
use yii\base\InvalidConfigException;
use yii\console\Controller;
use yii\db\Exception;
use yii\web\NotFoundHttpException;

class CrontabController extends Controller
{
    use ServiceTrait;

    /**
     * @throws InvalidConfigException
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function actionRecurrence()
    {
        $items = Recurrence::find()
            ->where(['status' => RecurrenceStatus::ACTIVE])
            ->andWhere(['execution_date' => Yii::$app->formatter->asDatetime('now', 'php:Y-m-d')])
            ->asArray()
            ->all();
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($items as $item) {
                \Yii::$app->user->setIdentity(User::findOne($item['user_id']));
                if ($t = $this->transactionService->copy($item['transaction_id'], $item['user_id'])) {
                    $keyboard = $this->telegramService->getRecordMarkup($t);
                    $text = $this->telegramService->getMessageTextByTransaction($t, '定时记账成功');
                    $this->telegramService->sendMessage($text, $keyboard);
                    $this->stdout("定时记账成功，transaction_id：{$t->id}\n");
                }
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $this->stdout("定时记账失败：{$e->getMessage()}\n");
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @param string $type
     * @throws Exception
     */
    public function actionReport(string $type = AnalysisDateType::YESTERDAY)
    {
        $items = AuthClient::find()
            ->where(['type' => AuthClientType::TELEGRAM])
            ->asArray()
            ->all();
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($items as $item) {
                \Yii::$app->user->setIdentity(User::findOne($item['user_id']));
                $text = $this->telegramService->getAnalysisTextByTransaction($type);
                $this->telegramService->sendMessage($text);
                $this->stdout("定时发送报告成功，user_id：{$item['user_id']}\n");
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $this->stdout("定时发送报告失败：{$e->getMessage()}\n");
            $transaction->rollBack();
            throw $e;
        }
    }
}
