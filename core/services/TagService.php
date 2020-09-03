<?php

namespace app\core\services;

use app\core\models\Tag;
use app\core\models\Transaction;
use Yii;
use yii\db\Exception;
use yii\db\Expression;
use yiier\helpers\DateHelper;
use yiier\helpers\Setup;

class TagService
{
    public static function getTagNames(int $userId = 0)
    {
        $userId = $userId ?: Yii::$app->user->id;
        return Tag::find()->select('name')->where(['user_id' => $userId])->column();
    }

    /**
     * @param array $data
     * @return Tag
     * @throws Exception
     */
    public function create(array $data)
    {
        $model = new Tag();
        $model->load($data, '');
        if (!$model->save(false)) {
            throw new Exception(Setup::errorMessage($model->firstErrors));
        }
        return $model;
    }


    public static function updateCounters(array $tags, int $userId = 0)
    {
        $userId = $userId ?: Yii::$app->user->id;
        foreach ($tags as $tag) {
            $count = Transaction::find()
                ->where(['user_id' => $userId])
                ->andWhere(new Expression('FIND_IN_SET(:tag, tags)'))->addParams([':tag' => $tag])
                ->count();
            Tag::updateAll(
                ['count' => $count, 'updated_at' => DateHelper::convert('now')],
                ['user_id' => $userId, 'name' => $tag]
            );
        }
    }
}
