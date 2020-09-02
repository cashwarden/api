<?php

namespace app\core\types;

class DirectionType extends BaseType
{
    public const EXPENSE = 1;
    public const INCOME = 2;

    public static function names(): array
    {
        return [
            self::EXPENSE => 'expense',
            self::INCOME => 'income',
        ];
    }
}
