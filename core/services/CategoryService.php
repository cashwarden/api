<?php

namespace app\core\services;

use app\core\models\Account;

class CategoryService
{
    public static function getDefaultCategory(int $userId)
    {
        return Account::find()
            ->where(['user_id' => $userId, 'default' => Account::DEFAULT])
            ->orderBy(['id' => SORT_ASC])
            ->asArray()
            ->one();
    }
}
