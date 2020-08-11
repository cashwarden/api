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

    public static function texts(): array
    {
        return [
            self::CASH => t('app', 'Cash'),
            self::DEBIT_CARD => t('app', 'Debit Card'),
            self::CREDIT_CARD => t('app', 'Credit Card'),
            self::SAVING_ACCOUNT => t('app', 'Saving Account'),
            self::INVESTMENT => t('app', 'Investment'),
        ];
    }

    public static function iconUrls(): array
    {
        return [
            self::CASH => 'https://img.icons8.com/dusk/64/000000/cash-.png',
            self::DEBIT_CARD => 'https://img.icons8.com/dusk/64/000000/bank-card-front-side.png',
            self::CREDIT_CARD => 'https://img.icons8.com/dusk/64/000000/mastercard-credit-card.png',
            self::SAVING_ACCOUNT => 'https://img.icons8.com/cotton/64/000000/money-box.png',
            self::INVESTMENT => 'https://img.icons8.com/doodle/64/000000/economic-improvement.png',
        ];
    }
}
