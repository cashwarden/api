<?php

namespace app\modules\v1\controllers;

use app\core\exceptions\InternalException;
use app\core\exceptions\InvalidArgumentException;
use app\core\models\Record;
use app\core\traits\ServiceTrait;
use app\core\types\RecordSource;
use app\core\types\TransactionType;
use Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;

/**
 * Record controller for the `v1` module
 */
class RecordController extends ActiveController
{
    use ServiceTrait;

    public $modelClass = Record::class;
    public $noAuthActions = [];
    public $partialMatchAttributes = ['name'];
    public $defaultOrder = ['date' => SORT_DESC, 'id' => SORT_DESC];
    public $stringToIntAttributes = ['transaction_type' => TransactionType::class];
    public $relations = ['transaction' => []];

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create'], $actions['update']);
        return $actions;
    }


    /**
     * @return ActiveDataProvider
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     * @throws InternalException
     */
    public function prepareDataProvider()
    {
        $dataProvider = parent::prepareDataProvider();
        if ($searchKeywords = trim(request('keyword'))) {
            $dataProvider->query->andWhere(
                "MATCH(`description`, `tags`, `remark`) AGAINST ('*$searchKeywords*' IN BOOLEAN MODE)"
            );
        }
        $dataProvider->setModels($this->transactionService->formatRecords($dataProvider->getModels()));
        return $dataProvider;
    }

    /**
     * @param array $params
     * @return array
     * @throws Exception
     */
    protected function formatParams(array $params)
    {
        if (($date = explode('~', data_get($params, 'date'))) && count($date) == 2) {
            $start = $date[0] . ' 00:00:00';
            $end = $date[1] . ' 23:59:59';
            $params['date'] = "{$start}~{$end}";
        }
        return $params;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function actionOverview()
    {
        return array_values($this->analysisService->recordOverview);
    }


    /**
     * @return array
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function actionAnalysis()
    {
        $transactionType = request('transaction_type', TransactionType::getName(TransactionType::EXPENSE));
        $date = request('date', Yii::$app->formatter->asDatetime('now'));

        return $this->analysisService->getRecordStatisticalData(
            $date,
            TransactionType::toEnumValue($transactionType)
        );
    }

    /**
     * @return array
     * @throws Exception
     */
    public function actionSources()
    {
        $items = [];
        $names = RecordSource::names();
        foreach ($names as $key => $name) {
            $items[] = ['type' => $key, 'name' => $name];
        }
        return $items;
    }
}
