<?php

namespace app\core\helpers;

use Yii;

class AnalysisHelper
{
    public const TODAY = 'today';
    public const YESTERDAY = 'yesterday';
    public const CURRENT_MONTH = 'current_month';
    public const GRAND_TOTAL = 'grand_total';


    public static function getItems()
    {
        return [
            self::TODAY,
            self::YESTERDAY,
            self::CURRENT_MONTH,
            self::GRAND_TOTAL,
        ];
    }

    public static function texts()
    {
        return [
            self::TODAY => Yii::t('app', 'Today'),
            self::YESTERDAY => Yii::t('app', 'Yesterday'),
            self::CURRENT_MONTH => Yii::t('app', 'Current month'),
            self::GRAND_TOTAL => Yii::t('app', 'Grand total')
        ];
    }
}
