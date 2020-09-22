<?php

namespace app\core\services;

use app\core\models\Tag;
use app\core\models\Transaction;
use Yii;
use yii\db\Exception;
use yii\db\Expression;
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
        $model->user_id = Yii::$app->user->id;
        if (!$model->save(false)) {
            throw new Exception(Setup::errorMessage($model->firstErrors));
        }
        return $model;
    }

    /**
     * @param array $tags
     * @param int $userId
     * @throws \yii\base\InvalidConfigException
     */
    public static function updateCounters(array $tags, int $userId = 0)
    {
        $userId = $userId ?: Yii::$app->user->id;
        foreach ($tags as $tag) {
            $count = TransactionService::countTransactionByTag($tag, $userId);
            Tag::updateAll(
                ['count' => $count, 'updated_at' => Yii::$app->formatter->asDatetime('now')],
                ['user_id' => $userId, 'name' => $tag]
            );
        }
    }

    /**
     * @param string $oldName
     * @param string $newName
     * @param int $userId
     * @throws \yii\base\InvalidConfigException
     */
    public static function updateTagName(string $oldName, string $newName, int $userId = 0)
    {
        $userId = $userId ?: Yii::$app->user->id;
        $items = Transaction::find()
            ->where(['user_id' => $userId])
            ->andWhere(new Expression('FIND_IN_SET(:tag, tags)'))->addParams([':tag' => $oldName])
            ->asArray()
            ->all();
        /** @var Transaction $item */
        foreach ($items as $item) {
            $tags = str_replace($oldName, $newName, $item['tags']);
            Transaction::updateAll(
                ['tags' => $tags, 'updated_at' => Yii::$app->formatter->asDatetime('now')],
                ['id' => $item['id']]
            );
        }
    }
}
