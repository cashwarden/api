<?php

namespace app\modules\v1\controllers;

use app\core\exceptions\InvalidArgumentException;
use app\core\models\Recurrence;
use app\core\requests\RecurrenceUpdateStatusRequest;
use app\core\traits\ServiceTrait;
use app\core\types\RecurrenceFrequency;
use Yii;
use yii\base\InvalidConfigException;
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
     * @throws InvalidConfigException
     */
    public function actionUpdateStatus(int $id): Recurrence
    {
        $params = Yii::$app->request->bodyParams;
        $model = new RecurrenceUpdateStatusRequest();
        /** @var RecurrenceUpdateStatusRequest $model */
        $model = $this->validate($model, $params);

        return $this->recurrenceService->updateStatus($id, $model->status);
    }


    /**
     * @return array
     * @throws \Exception
     */
    public function actionFrequencyTypes()
    {
        $items = [];
        $texts = RecurrenceFrequency::texts();
        foreach (RecurrenceFrequency::names() as $key => $name) {
            $items[] = ['type' => $name, 'name' => data_get($texts, $key)];
        }
        return $items;
    }
}
