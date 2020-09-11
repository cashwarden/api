<?php

namespace app\core\types;

class RecurrenceFrequency extends BaseType
{
    public const DAY = 1;
    public const WEEK = 2;
    public const MONTH = 3;
    public const YEAR = 4;
    public const WORKING_DAY = 5;
    public const LEGAL_WORKING_DAY = 6;


    public static function names(): array
    {
        return [
            self::DAY => 'day',
            self::WEEK => 'week',
            self::MONTH => 'month',
//            self::YEAR => 'year',
            self::WORKING_DAY => 'working_day',
            self::LEGAL_WORKING_DAY => 'legal_working_day',
        ];
    }

    public static function texts()
    {
        return [
            self::DAY => '每天',
            self::WEEK => '每周',
            self::MONTH => '每月',
//            self::YEAR => '每年',
            self::WORKING_DAY => '工作日（周一至周五）',
            self::LEGAL_WORKING_DAY => '法定工作日',
        ];
    }
}
