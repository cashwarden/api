<?php

namespace app\core\services;

use app\core\models\Category;

class CategoryService
{
    public static function getDefaultCategory(int $userId)
    {
        return Category::find()
            ->where(['user_id' => $userId, 'default' => Category::DEFAULT])
            ->orderBy(['id' => SORT_ASC])
            ->asArray()
            ->one();
    }
}
