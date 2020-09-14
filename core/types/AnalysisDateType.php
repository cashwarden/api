<?php

namespace app\core\types;

use Yii;
use yii\base\InvalidConfigException;
use yiier\helpers\DateHelper;

class AnalysisDateType
{
    public const TODAY = 'today';
    public const YESTERDAY = 'yesterday';
    public const CURRENT_MONTH = 'current_month';
    public const LAST_MONTH = 'last_month';
    public const GRAND_TOTAL = 'grand_total';

    public const EVERY_DAY_OF_MONTH = 'every_day_of_month';


    public static function getItems()
    {
        return [
            self::TODAY,
            self::YESTERDAY,
            self::CURRENT_MONTH,
            self::LAST_MONTH,
            self::GRAND_TOTAL,
        ];
    }

    public static function texts()
    {
        return [
            self::TODAY => Yii::t('app', 'Today'),
            self::YESTERDAY => Yii::t('app', 'Yesterday'),
            self::CURRENT_MONTH => Yii::t('app', 'Current month'),
            self::LAST_MONTH => Yii::t('app', 'Last month'),
            self::GRAND_TOTAL => Yii::t('app', 'Grand total')
        ];
    }


    /**
     * @param string $dateStr
     * @return array
     * @throws InvalidConfigException
     * @throws \Exception
     */
    public static function getEveryDayByMonth(string $dateStr)
    {
        $formatter = Yii::$app->formatter;
        $items = [];
        [$y, $m, $lastDay] = explode('-', date("Y-m-t", strtotime($dateStr)));
        for ($i = 1; $i <= $lastDay; $i++) {
            $time = date("{$y}-{$m}-" . sprintf("%02d", $i));
            $date = [DateHelper::beginTimestamp($time), DateHelper::endTimestamp($time)];
            $items[] = array_map(function ($i) use ($formatter) {
                return $formatter->asDatetime($i);
            }, $date);
        }
        return $items;
    }
}
