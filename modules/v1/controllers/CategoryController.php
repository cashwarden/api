<?php

namespace app\modules\v1\controllers;

use app\core\exceptions\InvalidArgumentException;
use app\core\helpers\AnalysisHelper;
use app\core\helpers\SearchHelper;
use app\core\models\Category;
use app\core\services\AnalysisService;
use app\core\traits\ServiceTrait;
use app\core\types\TransactionType;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yiier\helpers\SearchModel;

/**
 * Category controller for the `v1` module
 */
class CategoryController extends ActiveController
{
    use ServiceTrait;

    public $modelClass = Category::class;
    public $defaultOrder = ['sort' => SORT_ASC, 'id' => SORT_DESC];
    public $partialMatchAttributes = ['name'];


    /**
     * @return ActiveDataProvider
     * @throws InvalidConfigException|InvalidArgumentException
     * @throws \Exception
     */
    public function prepareDataProvider()
    {
        $modelClass = $this->modelClass;
        $searchModel = new SearchModel([
            'defaultOrder' => $this->defaultOrder,
            'model' => $modelClass,
            'scenario' => 'default',
            'partialMatchAttributes' => $this->partialMatchAttributes,
            'pageSize' => $this->getPageSize()
        ]);

        $params = Yii::$app->request->queryParams;
        if ($type = data_get($params, 'transaction_type')) {
            $params['transaction_type'] = SearchHelper::stringToInt($type, TransactionType::class);
        }

        $dataProvider = $searchModel->search(['SearchModel' => $params]);
        $dataProvider->query->andWhere(['user_id' => Yii::$app->user->id]);

        return $dataProvider;
    }

    /**
     * @return array
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function actionAnalysis()
    {
        $transactionType = request('transaction_type', TransactionType::getName(TransactionType::EXPENSE));
        $dateType = request('date_type', AnalysisHelper::CURRENT_MONTH);
        $date = AnalysisService::getDateRange($dateType);

        return $this->analysisService->getCategoryStatisticalData(
            $date,
            TransactionType::toEnumValue($transactionType)
        );
    }
}
