<?php

namespace app\modules\v1\controllers;

use app\core\exceptions\InvalidArgumentException;
use app\core\helpers\SearchHelper;
use app\core\models\Category;
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
}
