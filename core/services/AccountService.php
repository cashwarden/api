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
        $model = self::getCurrentUserAccount($accountId);
        $model->balance_cent = self::getCalculateAccountBalanceCent($accountId);
        if (!$model->save()) {
            throw new \yii\db\Exception('update account failure');
        }
        return true;
    }


    /**
     * @param int $accountId
     * @return int
     */
    public static function getCalculateAccountBalanceCent(int $accountId): int
    {
        $in = Record::find()->where([
            'account_id' => $accountId,
            'direction' => DirectionType::IN,
        ])->sum('amount_cent');

        $out = Record::find()->where([
            'account_id' => $accountId,
            'direction' => DirectionType::OUT,
        ])->sum('amount_cent');

        return ($out - $in) ?: 0;
    }
}
