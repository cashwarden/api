<?php

namespace app\core\types;

class CurrencyCode extends BaseType
{
    /**
     * https://www.science.co.il/international/Currency-codes.php
     * @return array
     */
    public static function names(): array
    {
        return [
            'CNY' => t('app', 'Yuan Renminbi'),
            'USD' => t('app', 'US Dollar'),
            'EUR' => t('app', 'Euro'),
            'GBP' => t('app', 'Pound Sterling'),
            'JPY' => t('app', 'Japanese Yen'),
            'AUD' => t('app', 'Australian Dollar'),
            'CAD' => t('app', 'Canadian Dollar'),
            'CHF' => t('app', 'Swiss Franc'),
            'HKD' => t('app', 'Hong Kong Dollar'),
            'SEK' => t('app', 'Swedish Krona'),
        ];
    }

    /**
     * @return array
     */
    public static function getKeys(): array
    {
        return array_keys(self::names());
    }
}
