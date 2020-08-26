<?php

namespace app\modules\v1\controllers;

use app\core\models\Record;
use app\core\traits\ServiceTrait;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yiier\helpers\SearchModel;

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
        unset($actions['create'], $actions['update']);
        return $actions;
    }

    /**
     * @return ActiveDataProvider
     * @throws InvalidConfigException
     */
    public function prepareDataProvider()
    {
        $modelClass = $this->modelClass;
        $searchModel = new SearchModel([
            'defaultOrder' => ['date' => SORT_DESC, 'id' => SORT_DESC],
            'model' => $modelClass,
            'scenario' => 'default',
            'pageSize' => $this->getPageSize()
        ]);

        $dataProvider = $searchModel->search(['SearchModel' => Yii::$app->request->queryParams]);
        $dataProvider->query->andWhere(['user_id' => Yii::$app->user->id]);

        $dataProvider->setModels($this->transactionService->formatRecords($dataProvider->getModels()));

        return $dataProvider;
    }
}
