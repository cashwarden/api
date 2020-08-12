<?php

namespace app\core\services;

use app\core\exceptions\InternalException;
use app\core\models\Account;
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

    public static function getDefaultAccount(int $userId)
    {
        return Account::find()
            ->where(['user_id' => $userId, 'default' => Account::DEFAULT])
            ->orderBy(['id' => SORT_ASC])
            ->asArray()
            ->one();
    }
}
