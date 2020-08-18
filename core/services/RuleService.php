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
        if (!$model = Rule::find()->where(['id' => $id, 'user_id' => \Yii::$app->user->id])->one()) {
            throw new NotFoundHttpException('No data found');
        }
        $rule = new Rule();
        $values = $model->toArray();
        $rule->load($values, '');
        $rule->name = $rule->name . ' Copy';
        if (!$rule->save(false)) {
            throw new Exception(Setup::errorMessage($model->firstErrors));
        }
        return Rule::findOne($rule->id);
    }
}
