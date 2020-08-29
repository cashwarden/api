<?php

namespace app\modules\v1\controllers;

use app\core\exceptions\InvalidArgumentException;
use app\core\helpers\SearchHelper;
use app\core\models\Record;
use app\core\traits\ServiceTrait;
use app\core\types\TransactionType;
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
     * @throws InvalidConfigException|InvalidArgumentException
     * @throws \Exception
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

        $params = Yii::$app->request->queryParams;
        if ($type = data_get($params, 'transaction_type')) {
            $params['transaction_type'] = SearchHelper::stringToInt($type, TransactionType::class);
        }

        $dataProvider = $searchModel->search(['SearchModel' => $params]);
        $dataProvider->query->andWhere(['user_id' => Yii::$app->user->id]);

        $dataProvider->setModels($this->transactionService->formatRecords($dataProvider->getModels()));

        return $dataProvider;
    }
}
