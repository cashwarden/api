<?php

namespace app\core\types;

class TransactionStatus extends BaseType
{
    /** @var int 待入账 */
    public const TODO = 0;

    /** @var int 已入账 */
    public const DONE = 1;

    public static function names(): array
    {
        return [
            self::TODO => 'todo',
            self::DONE => 'done',
        ];
    }
}
