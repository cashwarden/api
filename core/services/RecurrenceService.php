<?php

namespace app\core\services;

use app\core\exceptions\InvalidArgumentException;
use app\core\exceptions\ThirdPartyServiceErrorException;
use app\core\helpers\HolidayHelper;
use app\core\models\Recurrence;
use app\core\traits\SendRequestTrait;
use app\core\traits\ServiceTrait;
use app\core\types\RecurrenceFrequency;
use app\core\types\RecurrenceStatus;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\web\NotFoundHttpException;
use yiier\helpers\Setup;

class RecurrenceService extends BaseObject
{
    use SendRequestTrait;
    use ServiceTrait;

    /**
     * @param int $id
     * @param string $status
     * @return Recurrence
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws InvalidArgumentException
     */
    public function updateStatus(int $id, string $status): Recurrence
    {
        $model = $this->findCurrentOne($id);
        $model->load($model->toArray(), '');
        if (RecurrenceStatus::ACTIVE == RecurrenceStatus::toEnumValue($status)) {
            $model->started_at = strtotime($model->started_at) > time() ? $model->started_at : 'now';
        }
        $model->started_at = Yii::$app->formatter->asDate($model->started_at);
        $model->status = $status;
        if (!$model->save()) {
            throw new Exception(Setup::errorMessage($model->firstErrors));
        }
        return $model;
    }


    /**
     * @param int $id
     * @return Recurrence|object
     * @throws NotFoundHttpException
     */
    public function findCurrentOne(int $id): Recurrence
    {
        if (!$model = Recurrence::find()->where(['id' => $id, 'user_id' => \Yii::$app->user->id])->one()) {
            throw new NotFoundHttpException('No data found');
        }
        return $model;
    }

    /**
     * @param Recurrence $recurrence
     * @return string|null
     * @throws InvalidConfigException
     * @throws \Exception
     */
    public static function getExecutionDate(Recurrence $recurrence)
    {
        $formatter = Yii::$app->formatter;
        switch ($recurrence->frequency) {
            case RecurrenceFrequency::DAY:
                $date = strtotime("+1 day", strtotime($recurrence->started_at));
                break;
            case RecurrenceFrequency::WEEK:
                $currentWeekDay = $formatter->asDatetime('now', 'php:N') - 1;
                $weekDay = $recurrence->schedule;
                $addDay = $currentWeekDay > $weekDay ? 7 - $currentWeekDay + $weekDay : $weekDay - $currentWeekDay;
                $date = strtotime("+{$addDay} day", strtotime($recurrence->started_at));
                break;

            case RecurrenceFrequency::MONTH:
                $currDay = $formatter->asDatetime('now', 'php:d');
                $d = $recurrence->schedule;
                $date = Yii::$app->formatter->asDatetime($currDay > $d ? strtotime('+1 month') : time(), 'php:Y-m');
                $d = sprintf("%02d", $d);
                return "{$date}-{$d}";
            case RecurrenceFrequency::YEAR:
                $m = current(explode('-', $recurrence->schedule));
                $currMonth = $formatter->asDatetime('now', 'php:m');
                $y = Yii::$app->formatter->asDatetime($currMonth > $m ? strtotime('+1 year') : time(), 'php:Y');
                return "{$y}-{$recurrence->schedule}";
            case RecurrenceFrequency::WORKING_DAY:
                if (($currentWeekDay = $formatter->asDatetime('now', 'php:N') - 1) > 5) {
                    return null;
                }
                $date = strtotime("+1 day", strtotime($recurrence->started_at));
                break;
            case RecurrenceFrequency::LEGAL_WORKING_DAY:
                return HolidayHelper::getNextWorkday();
            default:
                return null;
        }
        return $formatter->asDatetime($date, 'php:Y-m-d');
    }

    /**
     * @param int $transactionId
     * @param int $userId
     * @return bool|int|string|null
     */
    public static function countByTransactionId(int $transactionId, int $userId)
    {
        return Recurrence::find()
            ->where(['user_id' => $userId, 'transaction_id' => $transactionId])
            ->count();
    }

    /**
     * @throws InvalidConfigException|ThirdPartyServiceErrorException
     */
    public static function updateAllExecutionDate()
    {
        $items = Recurrence::find()
            ->where(['status' => RecurrenceStatus::ACTIVE])
            ->andWhere(['!=', 'frequency', RecurrenceFrequency::LEGAL_WORKING_DAY])
            ->all();
        /** @var Recurrence $item */
        foreach ($items as $item) {
            $date = self::getExecutionDate($item);
            Recurrence::updateAll(
                ['execution_date' => $date, 'updated_at' => Yii::$app->formatter->asDatetime('now')],
                ['id' => $item->id]
            );
        }
        self::updateAllLegalWorkingDay();
    }

    /**
     * @throws InvalidConfigException
     * @throws ThirdPartyServiceErrorException
     */
    private static function updateAllLegalWorkingDay()
    {
        $items = Recurrence::find()
            ->where(['frequency' => RecurrenceFrequency::LEGAL_WORKING_DAY, 'status' => RecurrenceStatus::ACTIVE])
            ->asArray()
            ->all();
        $nextWorkday = HolidayHelper::getNextWorkday();
        foreach ($items as $key => $item) {
            Recurrence::updateAll(
                ['execution_date' => $nextWorkday, 'updated_at' => Yii::$app->formatter->asDatetime('now')],
                ['id' => $item['id']]
            );
        }
    }
}
