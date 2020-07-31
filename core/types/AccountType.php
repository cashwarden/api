<?php

namespace app\core\types;

class AccountType extends BaseType
{
    /** @var int Cash */
    public const CASH = 1;

    /** @var int Debit Card */
    public const DEBIT_CARD = 2;

    /** @var int Credit Card */
    public const CREDIT_CARD = 3;

    /** @var int Saving Account */
    public const SAVING_ACCOUNT = 4;

    /** @var int Investment */
    public const INVESTMENT = 5;

    public static function names(): array
    {
        return [
            self::CASH => 'cash',
            self::DEBIT_CARD => 'debit_card',
            self::CREDIT_CARD => 'credit_card',
            self::SAVING_ACCOUNT => 'saving_account',
            self::INVESTMENT => 'investment',
        ];
    }
}
