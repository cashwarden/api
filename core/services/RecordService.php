<?php

namespace app\core\services;

use app\core\exceptions\InternalException;
use app\core\models\Record;
use app\core\requests\RecordCreateByDescRequest;
use app\core\traits\ServiceTrait;
use app\core\types\DirectionType;
use Exception;
use Yii;
use yiier\helpers\Setup;

class RecordService
{
    use ServiceTrait;

    /**
     * @param RecordCreateByDescRequest $request
     * @return Record
     * @throws InternalException|\Throwable
     */
    public function createByDesc(RecordCreateByDescRequest $request): Record
    {
        $model = new Record();
        try {
            $model->description = $request->description;
            $model->user_id = Yii::$app->user->id;
            $rules = $this->getRuleService()->getRulesByDesc($model->description);
            $model->direction = $this->getDataByDesc(
                $rules,
                'then_direction',
                function () {
                    return DirectionType::getName(DirectionType::OUT);
                }
            );
            $direction = DirectionType::toEnumValue($model->direction);
            if (in_array($direction, [DirectionType::OUT, DirectionType::TRANSFER])) {
                $model->from_account_id = $this->getDataByDesc(
                    $rules,
                    'then_from_account_id',
                    [$this, 'getAccountIdByDesc']
                );
            }
            if (in_array($direction, [DirectionType::IN, DirectionType::TRANSFER])) {
                $model->to_account_id = $this->getDataByDesc(
                    $rules,
                    'then_to_account_id',
                    [$this, 'getAccountIdByDesc']
                );
            }

            $model->category_id = $this->getDataByDesc(
                $rules,
                'then_category_id',
                function () use ($direction) {
                    return (int)data_get(CategoryService::getDefaultCategory($direction), 'id', 0);
                }
            );

            $model->tags = $this->getDataByDesc($rules, 'then_tags');
            $model->transaction_status = $this->getDataByDesc($rules, 'then_transaction_status');
            $model->reimbursement_status = $this->getDataByDesc($rules, 'then_reimbursement_status');

            $model->amount = $this->getAmountByDesc($model->description);
            $model->currency_amount = $model->amount;
            $model->currency_code = user('base_currency_code');
            if (!$model->save(false)) {
                throw new \yii\db\Exception(Setup::errorMessage($model->firstErrors));
            }
        } catch (Exception $e) {
            Yii::error(
                ['request_id' => Yii::$app->requestId->id, $model->attributes, $model->errors, (string)$e],
                __FUNCTION__
            );
            throw new InternalException($e->getMessage());
        }
        return Record::findOne($model->id);
    }

    /**
     * @param string $desc
     * @return mixed|null
     * @throws Exception
     */
    public function getAmountByDesc(string $desc): int
    {
        preg_match_all('!\d+!', $desc, $matches);

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
