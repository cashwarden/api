<?php

namespace app\core\services;

use app\core\models\Category;
use Yii;

class CategoryService
{
    public static function getDefaultCategory(int $userId = 0)
    {
        $userId = $userId ?: Yii::$app->user->id;
        return Category::find()
            ->where(['user_id' => $userId, 'default' => Category::DEFAULT])
            ->orderBy(['id' => SORT_ASC])
            ->asArray()
            ->one();
    }
}
