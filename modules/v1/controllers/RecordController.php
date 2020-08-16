<?php

namespace app\modules\v1\controllers;

use app\core\models\Record;
use app\core\requests\RecordCreateByDescRequest;
use app\core\traits\ServiceTrait;
use Yii;

/**
 * Record controller for the `v1` module
 */
class RecordController extends ActiveController
{
    use ServiceTrait;

    public $modelClass = Record::class;
    public $noAuthActions = [];

    public function actions()
    {
        $actions = parent::actions();
        // 注销系统自带的实现方法
        unset($actions['update'], $actions['delete']);
        return $actions;
    }

    /**
     * @return Record
     * @throws \Exception|\Throwable
     */
    public function actionCreateByDescription()
    {
        $params = Yii::$app->request->bodyParams;
        $model = new RecordCreateByDescRequest();
        /** @var RecordCreateByDescRequest $model */
        $model = $this->validate($model, $params);

        return $this->recordService->createByDesc($model);
    }
}
