<?php

namespace app\core\types;

class ReimbursementStatus extends BaseType
{
    /** @var int */
    public const NONE = 0;

    /** @var int */
    public const TODO = 1;

    /** @var int */
    public const DONE = 2;

    public static function names(): array
    {
        return [
            self::NONE => 'none',
            self::TODO => 'todo',
            self::DONE => 'done',
        ];
    }
}
