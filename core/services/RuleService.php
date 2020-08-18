<?php

namespace app\core\services;

use app\core\models\Rule;
use yii\db\Exception;
use yii\web\NotFoundHttpException;
use yiier\helpers\Setup;

class RuleService
{
    /**
     * @param int $id
     * @return Rule
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function copy(int $id): Rule
    {
        $model = $this->findCurrentOne($id);
        $rule = new Rule();
        $values = $model->toArray();
        $rule->load($values, '');
        $rule->name = $rule->name . ' Copy';
        if (!$rule->save(false)) {
            throw new Exception(Setup::errorMessage($rule->firstErrors));
        }
        return Rule::findOne($rule->id);
    }

    /**
     * @param int $id
     * @param string $status
     * @return Rule
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function updateStatus(int $id, string $status)
    {
        $model = $this->findCurrentOne($id);
        $model->load($model->toArray(), '');
        $model->status = $status;
        if (!$model->save(false)) {
            throw new Exception(Setup::errorMessage($model->firstErrors));
        }
        return $model;
    }

    /**
     * @param int $id
     * @return Rule
     * @throws NotFoundHttpException
     */
    public function findCurrentOne(int $id): Rule
    {
        if (!$model = Rule::find()->where(['id' => $id, 'user_id' => \Yii::$app->user->id])->one()) {
            throw new NotFoundHttpException('No data found');
        }
        return $model;
    }
}
