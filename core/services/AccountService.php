<?php

namespace app\core\services;

use app\core\exceptions\InternalException;
use app\core\models\Account;
use app\core\models\Record;
use app\core\types\DirectionType;
use Exception;
use Yii;
use yii\db\ActiveRecord;
use yiier\helpers\Setup;

class AccountService
{
    /**
     * @param Account $account
     * @return Account
     * @throws InternalException
     */
    public function createUpdate(Account $account): Account
    {
        try {
            $account->user_id = Yii::$app->user->id;
            if (!$account->save(false)) {
                throw new \yii\db\Exception(Setup::errorMessage($account->firstErrors));
            }
        } catch (Exception $e) {
            Yii::error(
                ['request_id' => Yii::$app->requestId->id, $account->attributes, $account->errors, (string)$e],
                __FUNCTION__
            );
            throw new InternalException($e->getMessage());
        }
        return Account::findOne($account->id);
    }


    /**
     * @param int $id
     * @return Account|ActiveRecord|null
     */
    public static function getCurrentUserAccount(int $id)
    {
        return Account::find()->where(['id' => $id])
            ->andWhere(['user_id' => Yii::$app->user->id])
            ->one();
    }

    public static function getDefaultAccount(int $userId = 0)
    {
        $userId = $userId ?: Yii::$app->user->id;
        return Account::find()
            ->where(['user_id' => $userId, 'default' => Account::DEFAULT])
            ->orderBy(['id' => SORT_ASC])
            ->asArray()
            ->one();
    }

    /**
     * @param int $accountId
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function updateAccountBalance(int $accountId): bool
    {
        if (!$model = self::getCurrentUserAccount($accountId)) {
            throw new \yii\db\Exception('not found account');
        }
        $model->load($model->toArray(), '');
        $model->currency_balance = Setup::toYuan(self::getCalculateCurrencyBalanceCent($accountId));
        if (!$model->save()) {
            Yii::error(
                ['request_id' => Yii::$app->requestId->id, $model->attributes, $model->errors],
                __FUNCTION__
            );
            throw new \yii\db\Exception('update account failure ' . Setup::errorMessage($model->firstErrors));
        }
        return true;
    }


    /**
     * @param int $accountId
     * @return int
     */
    public static function getCalculateCurrencyBalanceCent(int $accountId): int
    {
        $in = Record::find()->where([
            'account_id' => $accountId,
            'direction' => DirectionType::IN,
        ])->sum('currency_amount_cent');

        $out = Record::find()->where([
            'account_id' => $accountId,
            'direction' => DirectionType::OUT,
        ])->sum('currency_amount_cent');

        return ($in - $out) ?: 0;
    }
}
