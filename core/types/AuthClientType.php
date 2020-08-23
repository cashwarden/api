<?php

namespace app\core\types;

class AuthClientType extends BaseType
{
    public const TELEGRAM = 1;

    public static function names(): array
    {
        return [
            self::TELEGRAM => 'telegram',
        ];
    }
}
