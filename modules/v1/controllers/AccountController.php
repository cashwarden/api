<?php

namespace app\modules\v1\controllers;

use app\core\models\Account;
use app\core\services\AccountService;
use app\core\traits\ServiceTrait;
use app\core\types\AccountType;
use app\core\types\ColorType;
use Yii;
use yii\web\NotFoundHttpException;
use yiier\helpers\SearchModel;

/**
 * Account controller for the `v1` module
 */
class AccountController extends ActiveController
{
    use ServiceTrait;

    public $modelClass = Account::class;
    public $noAuthActions = [];

    public function actions()
    {
        $actions = parent::actions();
        // 注销系统自带的实现方法
        unset($actions['update'], $actions['delete'], $actions['create']);
        return $actions;
    }

    /**
     * @return Account
     * @throws \Exception
     */
    public function actionCreate()
    {
        $params = Yii::$app->request->bodyParams;
        $model = new Account();
        $model->user_id = 0;
        if (data_get($params, 'type') == AccountType::CREDIT_CARD) {
            $model->setScenario(AccountType::CREDIT_CARD);
        }
        /** @var Account $model */
        $model = $this->validate($model, $params);
        $model->color = $model->color ?: array_rand(array_flip(ColorType::names()), 1);

        return $this->accountService->createUpdate($model);
    }

    /**
     * @return \yii\data\ActiveDataProvider
     */
    public function prepareDataProvider()
    {
        /** @var Account $modelClass */
        $modelClass = $this->modelClass;
        $searchModel = new SearchModel(
            [
                'defaultOrder' => ['id' => SORT_DESC],
                'model' => $modelClass::find()->where(['user_id' => Yii::$app->user->id]),
                'scenario' => 'default',
                'pageSize' => $this->getPageSize()
            ]
        );

        return $searchModel->search(['SearchModel' => Yii::$app->request->queryParams]);
    }

    /**
     * @param int $id
     * @return Account
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionUpdate(int $id)
    {
        $params = Yii::$app->request->bodyParams;
        if (!$model = AccountService::getCurrentUserAccount($id)) {
            throw new NotFoundHttpException();
        }

        if (data_get($params, 'type') == AccountType::CREDIT_CARD) {
            $model->setScenario(AccountType::CREDIT_CARD);
        }
        $model = $this->validate($model, $params);

        return $this->accountService->createUpdate($model);
    }
}
