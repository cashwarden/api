<?php

namespace app\modules\v1\controllers;

use app\core\models\Rule;
use app\core\traits\ServiceTrait;
use yii\db\Exception;
use yii\web\NotFoundHttpException;

/**
 * Rule controller for the `v1` module
 */
class RuleController extends ActiveController
{
    use ServiceTrait;

    public $modelClass = Rule::class;


    /**
     * @param int $id
     * @return Rule
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function actionCopy(int $id)
    {
        return $this->ruleService->copy($id);
    }
}
