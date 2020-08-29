<?php

namespace app\modules\v1\controllers;

use app\core\models\Transaction;
use app\core\requests\TransactionCreateByDescRequest;
use app\core\traits\ServiceTrait;
use app\core\types\TransactionType;
use Yii;

/**
 * Transaction controller for the `v1` module
 */
class TransactionController extends ActiveController
{
    use ServiceTrait;

    public $modelClass = Transaction::class;
    public $noAuthActions = [];

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index'], $actions['delete']);
        return $actions;
    }

    /**
     * @return Transaction
     * @throws \Exception|\Throwable
     */
    public function actionCreateByDescription()
    {
        $params = Yii::$app->request->bodyParams;
        $model = new TransactionCreateByDescRequest();
        /** @var TransactionCreateByDescRequest $model */
        $model = $this->validate($model, $params);

        return $this->transactionService->createByDesc($model->description);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function actionTypes()
    {
        $items = [];
        $texts = TransactionType::texts();
        $names = TransactionType::names();
        // unset($names[TransactionType::ADJUST]);
        foreach ($names as $key => $name) {
            $items[] = ['type' => $name, 'name' => data_get($texts, $key)];
        }
        return $items;
    }
}
