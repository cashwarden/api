<?php

namespace app\core\types;

class DirectionType extends BaseType
{
    public const OUT = 1;
    public const IN = 2;

    public static function names(): array
    {
        return [
            self::OUT => 'out',
            self::IN => 'in',
        ];
    }
}
