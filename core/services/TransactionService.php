<?php

namespace app\core\services;

use app\core\exceptions\InternalException;
use app\core\helpers\ArrayHelper;
use app\core\models\Account;
use app\core\models\Record;
use app\core\models\Tag;
use app\core\models\Transaction;
use app\core\traits\ServiceTrait;
use app\core\types\DirectionType;
use app\core\types\TransactionType;
use Exception;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yiier\graylog\Log;
use yiier\helpers\Setup;

/**
 * @property-read int $accountIdByDesc
 */
class TransactionService extends BaseObject
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
        if (in_array($transaction->type, [TransactionType::EXPENSE, TransactionType::TRANSFER])) {
            array_push($data, ['direction' => DirectionType::EXPENSE, 'account_id' => $transaction->from_account_id]);
        }
        if (in_array($transaction->type, [TransactionType::INCOME, TransactionType::TRANSFER])) {
            array_push($data, ['direction' => DirectionType::INCOME, 'account_id' => $transaction->to_account_id]);
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
            $_model->transaction_type = $transaction->type;
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
            $direction = $type == TransactionType::INCOME ? DirectionType::EXPENSE : DirectionType::INCOME;
            Record::find()->where([
                'transaction_id' => $transaction->id,
                'direction' => $direction
            ])->one()->delete();
        }
    }


    /**
     * @param string $desc
     * @param null|int $source
     * @return Transaction
     * @throws InternalException
     * @throws \Throwable
     */
    public function createByDesc(string $desc, $source = null): Transaction
    {
        $model = new Transaction();
        try {
            $model->description = $desc;
            $model->user_id = Yii::$app->user->id;
            $rules = $this->getRuleService()->getRulesByDesc($desc);
            $model->type = $this->getDataByDesc(
                $rules,
                'then_transaction_type',
                function () {
                    return TransactionType::getName(TransactionType::EXPENSE);
                }
            );
            $transactionType = TransactionType::toEnumValue($model->type);

            if (in_array($transactionType, [TransactionType::EXPENSE, TransactionType::TRANSFER])) {
                $model->from_account_id = $this->getDataByDesc(
                    $rules,
                    'then_from_account_id',
                    [$this, 'getAccountIdByDesc']
                );
            }


            if (in_array($transactionType, [TransactionType::INCOME, TransactionType::TRANSFER])) {
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

            $model->date = $this->getDateByDesc($desc);

            $model->tags = $this->getDataByDesc($rules, 'then_tags');
            $model->status = $this->getDataByDesc($rules, 'then_transaction_status');
            $model->reimbursement_status = $this->getDataByDesc($rules, 'then_reimbursement_status');

            $model->currency_amount = $this->getAmountByDesc($desc);
            $model->currency_code = user('base_currency_code');
            if (!$model->save()) {
                throw new \yii\db\Exception(Setup::errorMessage($model->firstErrors));
            }
            $source ? Record::updateAll(['source' => $source], ['transaction_id' => $model->id]) : null;
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
     * @param Record[] $records
     * @return array
     * @throws InvalidConfigException
     */
    public function formatRecords(array $records)
    {
        $items = [];
        foreach ($records as $record) {
            $key = Yii::$app->formatter->asDatetime(strtotime($record->date), 'php:Y-m-d');
            $items[$key]['records'][] = $record;
            $items[$key]['date'] = $key;
            if ($record->transaction_id) {
                // todo 计算有待优化
                if ($record->direction === DirectionType::EXPENSE) {
                    $items[$key]['record_out_amount_cent'][] = $record->amount_cent;
                    $items[$key]['out'] = Setup::toYuan(array_sum($items[$key]['record_out_amount_cent']));
                }
                if ($record->direction === DirectionType::INCOME) {
                    $items[$key]['record_in_amount_cent'][] = $record->amount_cent;
                    $items[$key]['in'] = Setup::toYuan(array_sum($items[$key]['record_in_amount_cent']));
                }
            }
        }
        return $items;
    }

    /**
     * @param int $id
     * @param int $rating
     * @return int
     * @throws InvalidConfigException
     */
    public function updateRating(int $id, int $rating)
    {
        return Transaction::updateAll(
            ['rating' => $rating, 'updated_at' => Yii::$app->formatter->asDatetime('now')],
            ['id' => $id]
        );
    }

    /**
     * @param Account $account
     * @return bool
     * @throws \yii\db\Exception
     * @throws InvalidConfigException
     */
    public static function createAdjustRecord(Account $account)
    {
        $diff = $account->currency_balance_cent - AccountService::getCalculateCurrencyBalanceCent($account->id);
        if (!$diff) {
            return false;
        }
        $model = new Record();
        $model->direction = $diff > 0 ? DirectionType::INCOME : DirectionType::EXPENSE;
        $model->currency_amount_cent = abs($diff);
        $model->user_id = $account->user_id;
        $model->account_id = $account->id;
        $model->transaction_id = 0;
        $model->transaction_type = TransactionType::ADJUST;
        $model->category_id = CategoryService::getAdjustCategoryId();
        $model->currency_code = $account->currency_code;
        $model->date = Yii::$app->formatter->asDatetime('now');
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
     * @return array
     * @throws Exception
     */
    public static function matchTagsByDesc(string $desc): array
    {
        if ($tags = TagService::getTagNames()) {
            $tags = implode('|', $tags);
            preg_match_all("!({$tags})!", $desc, $matches);
            return data_get($matches, '0', []);
        }
        return [];
    }

    /**
     * @param array $tags
     * @throws InvalidConfigException
     */
    public static function createTags(array $tags)
    {
        $has = Tag::find()
            ->select('name')
            ->where(['user_id' => Yii::$app->user->id, 'name' => $tags])
            ->column();
        /** @var TagService $tagService */
        $tagService = Yii::createObject(TagService::class);
        foreach (array_diff($tags, $has) as $item) {
            try {
                $tagService->create(['name' => $item]);
            } catch (Exception $e) {
                Log::error('add tag fail', [$item, (string)$e]);
            }
        }
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
    private function getDataByDesc(array $rules, string $field, $callback = null)
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

    /**
     * @param string $desc
     * @return string date Y-m-d
     * @throws InvalidConfigException
     */
    private function getDateByDesc(string $desc): string
    {
        if (ArrayHelper::strPosArr($desc, ['昨天', '昨日']) !== false) {
            return $this->getCreateRecordDate(time() - 3600 * 24 * 1);
        }

        if (ArrayHelper::strPosArr($desc, ['前天']) !== false) {
            return $this->getCreateRecordDate(time() - 3600 * 24 * 2);
        }

        if (ArrayHelper::strPosArr($desc, ['大前天']) !== false) {
            return $this->getCreateRecordDate(time() - 3600 * 24 * 3);
        }

        try {
            $time = $this->getCreateRecordDate('now', 'H:i');
            preg_match_all('!([0-9]+)(月)([0-9]+)(号|日)!', $desc, $matches);
            if (($m = data_get($matches, '1.0')) && $d = data_get($matches, '3.0')) {
                $currMonth = Yii::$app->formatter->asDatetime('now', 'php:m');
                $y = Yii::$app->formatter->asDatetime($m > $currMonth ? strtotime('-1 year') : time(), 'php:Y');
                $m = sprintf("%02d", $m);
                $d = sprintf("%02d", $d);
                return "{$y}-{$m}-{$d} {$time}";
            }

            preg_match_all('!([0-9]+)(号|日)!', $desc, $matches);
            if ($d = data_get($matches, '1.0')) {
                $y = Yii::$app->formatter->asDatetime(time(), 'php:Y');
                $currDay = Yii::$app->formatter->asDatetime('now', 'php:d');
                $m = Yii::$app->formatter->asDatetime($d > $currDay ? strtotime('-1 month') : time(), 'php:m');
                $d = sprintf("%02d", $d);
                return "{$y}-{$m}-{$d} {$time}";
            }
        } catch (Exception $e) {
            Log::warning('未识别到日期', $desc);
        }

        return $this->getCreateRecordDate();
    }


    /**
     * @param string $value
     * @param string $format
     * @return string
     * @throws InvalidConfigException
     */
    public function getCreateRecordDate(string $value = 'now', string $format = 'php:Y-m-d H:i')
    {
        return Yii::$app->formatter->asDatetime($value, $format);
    }
}
