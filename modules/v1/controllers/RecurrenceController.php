<?php

namespace app\modules\v1\controllers;

use app\core\exceptions\InvalidArgumentException;
use app\core\models\Recurrence;
use app\core\requests\RuleUpdateStatusRequest;
use app\core\traits\ServiceTrait;
use Yii;
use yii\db\Exception;
use yii\web\NotFoundHttpException;

/**
 * Recurrence controller for the `v1` module
 */
class RecurrenceController extends ActiveController
{
    use ServiceTrait;

    public $modelClass = Recurrence::class;
    public $partialMatchAttributes = ['name'];

    /**
     * @param int $id
     * @return Recurrence
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NotFoundHttpException
     */
    public function actionUpdateStatus(int $id): Recurrence
    {
        $params = Yii::$app->request->bodyParams;
        $model = new RuleUpdateStatusRequest();
        /** @var RuleUpdateStatusRequest $model */
        $model = $this->validate($model, $params);

        return $this->recurrenceService->updateStatus($id, $model->status);
    }
}
