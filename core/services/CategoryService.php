<?php

namespace app\core\services;

use app\core\models\Account;
use app\core\models\Category;
use app\core\types\TransactionType;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

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

    public static function getAdjustCategoryId(int $userId = 0)
    {
        $userId = $userId ?: Yii::$app->user->id;
        return Category::find()
            ->where(['user_id' => $userId, 'transaction_type' => TransactionType::ADJUST])
            ->orderBy(['id' => SORT_ASC])
            ->scalar();
    }

    /**
     * @param int $id
     * @return Account|ActiveRecord|null
     * @throws NotFoundHttpException
     */
    public static function findCurrentOne(int $id)
    {
        if (!$model = Category::find()->where(['id' => $id, 'user_id' => \Yii::$app->user->id])->one()) {
            throw new NotFoundHttpException('No data found');
        }
        return $model;
    }

    /**
     * @return array
     */
    public static function getCurrentMap()
    {
        $categories = Category::find()->where(['user_id' => Yii::$app->user->id])->asArray()->all();
        return ArrayHelper::map($categories, 'id', 'name');
    }
}
