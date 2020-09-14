<?php

namespace app\core\types;

class TelegramKeyword
{
    public const BIND = '/bind';
    public const START = '/start';
    public const CMD = '/cmd';
    public const TODAY = '/today';
    public const YESTERDAY = '/yesterday';
    public const LAST_MONTH = '/last_month';

    /**
     * @return string[]
     */
    public static function items()
    {
        return [
            self::BIND,
            self::START,
            self::CMD,
            self::TODAY,
            self::YESTERDAY,
            self::LAST_MONTH,
        ];
    }
}
