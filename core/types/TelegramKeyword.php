<?php

namespace app\core\types;

class TelegramKeyword
{
    public const BIND = '/bind';
    public const START = '/start';

    /**
     * @return string[]
     */
    public static function items()
    {
        return [
            self::BIND,
            self::START,
        ];
    }
}
