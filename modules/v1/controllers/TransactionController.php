<?php

namespace app\modules\v1\controllers;

use app\core\exceptions\InvalidArgumentException;
use app\core\models\Transaction;
use app\core\requests\TransactionCreateByDescRequest;
use app\core\requests\TransactionUploadRequest;
use app\core\traits\ServiceTrait;
use app\core\types\TransactionType;
use Yii;
use yii\web\UploadedFile;

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


    /**
     * @return array
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function actionUpload()
    {
        $fileParam = 'file';
        $uploadedFile = UploadedFile::getInstanceByName($fileParam);
        $params = [$fileParam => $uploadedFile];
        $model = new TransactionUploadRequest();
        $this->validate($model, $params);
        $filename = Yii::$app->user->id . 'record.csv';
        $this->uploadService->uploadRecord($uploadedFile, $filename);
        $data = $this->transactionService->createByCSV($filename);
        $this->uploadService->deleteLocalFile($filename);
        return $data;
    }


    /**
     * @return array
     * @throws \Exception
     */
    public function actionExport()
    {
        return $this->transactionService->exportData();
    }
}
