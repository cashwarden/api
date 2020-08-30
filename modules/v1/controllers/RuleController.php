<?php

namespace app\modules\v1\controllers;

use app\core\exceptions\InvalidArgumentException;
use app\core\models\Rule;
use app\core\requests\RuleUpdateStatusRequest;
use app\core\traits\ServiceTrait;
use Yii;
use yii\db\Exception;
use yii\web\NotFoundHttpException;

/**
 * Rule controller for the `v1` module
 */
class RuleController extends ActiveController
{
    use ServiceTrait;

    public $modelClass = Rule::class;
    public $defaultOrder = ['sort' => SORT_ASC, 'id' => SORT_DESC];
    public $partialMatchAttributes = ['name'];

    /**
     * @param int $id
     * @return Rule
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function actionCopy(int $id): Rule
    {
        return $this->ruleService->copy($id);
    }

    /**
     * @param int $id
     * @return Rule
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NotFoundHttpException
     */
    public function actionUpdateStatus(int $id): Rule
    {
        $params = Yii::$app->request->bodyParams;
        $model = new RuleUpdateStatusRequest();
        /** @var RuleUpdateStatusRequest $model */
        $model = $this->validate($model, $params);

        return $this->ruleService->updateStatus($id, $model->status);
    }
}
