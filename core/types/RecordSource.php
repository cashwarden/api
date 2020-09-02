<?php

namespace app\core\types;

class RecordSource extends BaseType
{
    public const WEB = 1;
    public const TELEGRAM = 2;
    public const CRONTAB = 3;

    public static function names(): array
    {
        return [
            self::WEB => 'web',
            self::TELEGRAM => 'telegram',
            self::CRONTAB => 'crontab',
        ];
    }
}
