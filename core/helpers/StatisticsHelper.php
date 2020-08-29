<?php

namespace app\core\helpers;

class StatisticsHelper
{
    public const TODAY = 'today';
    public const YESTERDAY = 'yesterday';
    public const MONTH = 'month';
    public const ALL = 'all';


    public static function getItems()
    {
        return [
            self::TODAY,
            self::YESTERDAY,
            self::MONTH,
            self::ALL,
        ];
    }
}
