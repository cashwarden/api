<?php

namespace app\core\types;

use Yii;

class AccountType extends BaseType
{
    /** @var int General Account */
    public const GENERAL_ACCOUNT = 0;

    /** @var int Cash Account */
    public const CASH_ACCOUNT = 1;

    /** @var int Debit Card */
    public const DEBIT_CARD = 2;

    /** @var int Credit Card */
    public const CREDIT_CARD = 3;

    /** @var int Saving Account */
    public const SAVING_ACCOUNT = 4;

    /** @var int Investment Account */
    public const INVESTMENT_ACCOUNT = 5;

    public static function names(): array
    {
        return [
            self::GENERAL_ACCOUNT => 'general_account',
            self::CASH_ACCOUNT => 'cash_account',
            self::DEBIT_CARD => 'debit_card',
            self::CREDIT_CARD => 'credit_card',
            self::SAVING_ACCOUNT => 'saving_account',
            self::INVESTMENT_ACCOUNT => 'investment_account',
        ];
    }

    public static function texts(): array
    {
        return [
            self::GENERAL_ACCOUNT => Yii::t('app', 'General Account'),
            self::CASH_ACCOUNT => Yii::t('app', 'Cash Account'),
            self::DEBIT_CARD => Yii::t('app', 'Debit Card'),
            self::CREDIT_CARD => Yii::t('app', 'Credit Card'),
            self::SAVING_ACCOUNT => Yii::t('app', 'Saving Account'),
            self::INVESTMENT_ACCOUNT => Yii::t('app', 'Investment Account'),
        ];
    }
}
