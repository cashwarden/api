<?php

namespace app\modules\v1\controllers;

use app\core\models\Account;
use app\core\services\AccountService;
use app\core\traits\ServiceTrait;
use app\core\types\AccountType;
use Exception;
use Yii;
use yii\web\NotFoundHttpException;
use yiier\helpers\Setup;

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
        unset($actions['update'], $actions['create']);
        return $actions;
    }

    /**
     * @return Account
     * @throws Exception
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

        return $this->accountService->createUpdate($model);
    }

    /**
     * @param int $id
     * @return Account
     * @throws NotFoundHttpException
     * @throws Exception
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
        /** @var Account $model */
        $model = $this->validate($model, $params);

        return $this->accountService->createUpdate($model);
    }


    /**
     * @return array
     * @throws Exception
     */
    public function actionTypes()
    {
        $items = [];
        $texts = AccountType::texts();
        foreach (AccountType::names() as $key => $name) {
            $items[] = ['type' => $name, 'name' => data_get($texts, $key)];
        }
        return $items;
    }

    /**
     * @return array
     */
    public function actionStatistics()
    {
        $balanceCentSum = Account::find()
            ->where(['user_id' => Yii::$app->user->id, 'exclude_from_stats' => false])
            ->sum('balance_cent');
        $items['net_asset'] = $balanceCentSum ? Setup::toYuan($balanceCentSum) : 0;

        $balanceCentSum = Account::find()
            ->where(['user_id' => Yii::$app->user->id, 'exclude_from_stats' => false])
            ->andWhere(['>', 'balance_cent', 0])
            ->sum('balance_cent');
        $items['total_assets'] = $balanceCentSum ? Setup::toYuan($balanceCentSum) : 0;

        $balanceCentSum = Account::find()
            ->where(['user_id' => Yii::$app->user->id, 'exclude_from_stats' => false])
            ->andWhere(['<', 'balance_cent', 0])
            ->sum('balance_cent');
        $items['liabilities'] = $balanceCentSum ? Setup::toYuan($balanceCentSum) : 0;

        $items['count'] = Account::find()
            ->where(['user_id' => Yii::$app->user->id, 'exclude_from_stats' => false])
            ->count('id');

        return $items;
    }
}
