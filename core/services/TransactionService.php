<?php

namespace app\core\services;

use app\core\exceptions\InternalException;
use app\core\models\Account;
use app\core\models\Record;
use app\core\models\Transaction;
use app\core\traits\ServiceTrait;
use app\core\types\DirectionType;
use app\core\types\TransactionType;
use Exception;
use Yii;
use yiier\helpers\Setup;

class TransactionService
{
    use ServiceTrait;

    /**
     * @param Transaction $transaction
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function createUpdateRecord(Transaction $transaction)
    {
        $data = [];
        if (in_array($transaction->type, [TransactionType::OUT, TransactionType::TRANSFER])) {
            array_push($data, ['direction' => DirectionType::OUT, 'account_id' => $transaction->from_account_id]);
        }
        if (in_array($transaction->type, [TransactionType::IN, TransactionType::TRANSFER])) {
            array_push($data, ['direction' => DirectionType::IN, 'account_id' => $transaction->to_account_id]);
        }
        $model = new Record();
        foreach ($data as $datum) {
            $conditions = ['transaction_id' => $transaction->id, 'direction' => $datum['direction']];
            if (!$_model = Record::find()->where($conditions)->one()) {
                $_model = clone $model;
            }
            $_model->user_id = $transaction->user_id;
            $_model->transaction_id = $transaction->id;
            $_model->category_id = $transaction->category_id;
            $_model->amount_cent = $transaction->amount_cent;
            $_model->currency_amount_cent = $transaction->currency_amount_cent;
            $_model->currency_code = $transaction->currency_code;
            $_model->date = $transaction->date;
            $_model->load($datum, '');
            if (!$_model->save()) {
                throw new \yii\db\Exception(Setup::errorMessage($_model->firstErrors));
            }
        }
        return true;
    }

    /**
     * @param Transaction $transaction
     * @param array $changedAttributes
     * @throws Exception|\Throwable
     */
    public static function deleteRecord(Transaction $transaction, array $changedAttributes)
    {
        $type = $transaction->type;
        if (data_get($changedAttributes, 'type') && $transaction->type !== TransactionType::TRANSFER) {
            $direction = $type == TransactionType::IN ? DirectionType::OUT : DirectionType::IN;
            Record::find()->where([
                'transaction_id' => $transaction->id,
                'direction' => $direction
            ])->one()->delete();
        }
    }


    /**
     * @param string $description
     * @return Transaction
     * @throws InternalException
     * @throws \Throwable
     */
    public function createByDesc(string $description): Transaction
    {
        $model = new Transaction();
        try {
            $model->description = $description;
            $model->user_id = Yii::$app->user->id;
            $rules = $this->getRuleService()->getRulesByDesc($description);
            $model->type = $this->getDataByDesc(
                $rules,
                'then_transaction_type',
                function () {
                    return TransactionType::getName(TransactionType::OUT);
                }
            );
            $transactionType = TransactionType::toEnumValue($model->type);

            if (in_array($transactionType, [TransactionType::OUT, TransactionType::TRANSFER])) {
                $model->from_account_id = $this->getDataByDesc(
                    $rules,
                    'then_from_account_id',
                    [$this, 'getAccountIdByDesc']
                );
            }


            if (in_array($transactionType, [TransactionType::IN, TransactionType::TRANSFER])) {
                $model->to_account_id = $this->getDataByDesc(
                    $rules,
                    'then_to_account_id',
                    [$this, 'getAccountIdByDesc']
                );
            }

            $model->category_id = $this->getDataByDesc(
                $rules,
                'then_category_id',
                function () {
                    //  todo 根据交易类型查找默认分类
                    return (int)data_get(CategoryService::getDefaultCategory(), 'id', 0);
                }
            );

            $model->tags = $this->getDataByDesc($rules, 'then_tags');
            $model->status = $this->getDataByDesc($rules, 'then_transaction_status');
            $model->reimbursement_status = $this->getDataByDesc($rules, 'then_reimbursement_status');

            $model->currency_amount = $this->getAmountByDesc($description);
            $model->currency_code = user('base_currency_code');
            if (!$model->save(false)) {
                throw new \yii\db\Exception(Setup::errorMessage($model->firstErrors));
            }
            return $model;
        } catch (Exception $e) {
            Yii::error(
                ['request_id' => Yii::$app->requestId->id, $model->attributes, $model->errors, (string)$e],
                __FUNCTION__
            );
            throw new InternalException($e->getMessage());
        }
    }

    /**
     * @param Account $account
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function createAdjustRecord(Account $account)
    {
        $diff = $account->currency_balance_cent - AccountService::getCalculateCurrencyBalanceCent($account->id);
        if (!$diff) {
            return false;
        }
        $model = new Record();
        $model->direction = $diff > 0 ? DirectionType::IN : DirectionType::OUT;
        $model->currency_amount_cent = abs($diff);
        $model->user_id = $account->user_id;
        $model->account_id = $account->id;
        $model->transaction_id = 0;
        $model->category_id = CategoryService::getAdjustCategoryId();
        $model->currency_code = $account->currency_code;
        $model->date = date('Y-m-d');
        if (!$model->save()) {
            Yii::error(
                ['request_id' => Yii::$app->requestId->id, $model->attributes, $model->errors],
                __FUNCTION__
            );
            throw new \yii\db\Exception(Setup::errorMessage($model->firstErrors));
        }
        return true;
    }

    /**
     * @param string $desc
     * @return mixed|null
     * @throws Exception
     */
    public function getAmountByDesc(string $desc): float
    {
        // todo 支持简单的算数
        preg_match_all('!([0-9]+(?:\.[0-9]{1,2})?)!', $desc, $matches);

        if (count($matches[0])) {
            return array_pop($matches[0]);
        }
        return 0;
    }

    /**
     * @param array $rules
     * @param string $field
     * @param \Closure|array|null $callback
     * @return null|int|string
     * @throws Exception
     */
    public function getDataByDesc(array $rules, string $field, $callback = null)
    {
        foreach ($rules as $rule) {
            if ($data = data_get($rule->toArray(), $field)) {
                return $data;
            }
        }
        return $callback ? call_user_func($callback) : null;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function getAccountIdByDesc(): int
    {
        $userId = Yii::$app->user->id;
        return (int)data_get(AccountService::getDefaultAccount($userId), 'id', 0);
    }
}
