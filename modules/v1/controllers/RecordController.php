<?php

namespace app\modules\v1\controllers;

use app\core\exceptions\InternalException;
use app\core\exceptions\InvalidArgumentException;
use app\core\helpers\AnalysisHelper;
use app\core\models\Record;
use app\core\services\AnalysisService;
use app\core\traits\ServiceTrait;
use app\core\types\TransactionType;
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
        $dataProvider->setModels($this->transactionService->formatRecords($dataProvider->getModels()));
        return $dataProvider;
    }


    /**
     * @return array
     * @throws \Exception
     */
    public function actionOverview()
    {
        $items = [];
        foreach (AnalysisHelper::texts() as $key => $item) {
            $date = AnalysisService::getDateRange($key);
            $items[$key]['overview'] = $this->analysisService->getRecordOverviewByDate($date);
            $items[$key]['key'] = $key;
            $items[$key]['text'] = $item;
        }

        return array_values($items);
    }


    /**
     * @return array
     * @throws InvalidArgumentException
     * @throws \Exception
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
}
