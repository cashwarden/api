<?php

namespace app\core\services;

use app\core\exceptions\InternalException;
use app\core\models\Record;
use app\core\requests\RecordCreateByDescRequest;
use app\core\types\DirectionType;
use Exception;
use Yii;
use yiier\helpers\Setup;

class RecordService
{
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
            $model->account_id = $this->getAccountIdByDesc($model->description);
            $model->category_id = $this->getCategoryIdByDesc($model->description);
            $model->direction = $this->getDirectionByDesc($model->description);
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

    public function getDirectionByDesc(string $desc)
    {
        return DirectionType::OUT;
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
     * @param string $desc
     * @return mixed|null
     * @throws Exception
     */
    public function getAccountIdByDesc(string $desc)
    {
        $userId = Yii::$app->user->id;
        return data_get(AccountService::getDefaultAccount($userId), 'id');
    }

    /**
     * @param string $desc
     * @return mixed|null
     * @throws Exception
     */
    public function getCategoryIdByDesc(string $desc)
    {
        $userId = Yii::$app->user->id;
        return data_get(CategoryService::getDefaultCategory($userId), 'id');
    }
}
