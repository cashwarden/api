<?php

namespace app\core\services;

use app\core\helpers\StatisticsHelper;
use app\core\models\Record;
use app\core\types\DirectionType;
use Yii;
use yii\base\BaseObject;
use yiier\helpers\DateHelper;
use yiier\helpers\Setup;

class StatisticsService extends BaseObject
{
    public function getRecordOverviewByDate(array $date): array
    {
        $conditions = [];
        if (count($date) == 2) {
            $conditions = ['between', 'date', $date[0], $date[1]];
        }
        $userId = \Yii::$app->user->id;
        $sum = Record::find()
            ->where(['user_id' => $userId, 'direction' => DirectionType::INCOME])
            ->andWhere($conditions)
            ->sum('amount_cent');
        $items['income'] = $sum ? (float)Setup::toYuan($sum) : 0;

        $sum = Record::find()
            ->where(['user_id' => $userId, 'direction' => DirectionType::EXPENSE])
            ->andWhere($conditions)
            ->sum('amount_cent');
        $items['expense'] = $sum ? (float)Setup::toYuan($sum) : 0;

        $items['surplus'] = (float)bcadd($items['income'], $items['expense'], 2);

        return $items;
    }


    /**
     * @param $key
     * @return array
     * @throws \Exception
     */
    public static function getDateRange($key): array
    {
        $formatter = Yii::$app->formatter;
        $date = [];
        switch ($key) {
            case StatisticsHelper::TODAY:
                $date = [DateHelper::beginTimestamp(), DateHelper::endTimestamp()];
                break;
            case StatisticsHelper::YESTERDAY:
                $time = strtotime('-1 day');
                $date = [DateHelper::beginTimestamp($time), DateHelper::endTimestamp($time)];
                break;
            case StatisticsHelper::MONTH:
                $time = $formatter->asDate('now', 'php:01-m-Y');
                $date = [DateHelper::beginTimestamp($time), DateHelper::endTimestamp()];
                break;
        }

        return array_map(function ($i) use ($formatter) {
            return $formatter->asDate($i);
        }, $date);
    }
}
