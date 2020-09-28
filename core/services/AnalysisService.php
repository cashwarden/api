<?php

namespace app\core\services;

use app\core\exceptions\InvalidArgumentException;
use app\core\models\Category;
use app\core\models\Record;
use app\core\models\Transaction;
use app\core\types\AnalysisDateType;
use app\core\types\DirectionType;
use app\core\types\TransactionType;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yiier\helpers\DateHelper;
use yiier\helpers\Setup;

/**
 *
 * @property-read array $recordOverview
 */
class AnalysisService extends BaseObject
{
    /**
     * @return array
     * @throws \Exception
     */
    public function getRecordOverview(): array
    {
        $items = [];
        foreach (AnalysisDateType::texts() as $key => $item) {
            $date = AnalysisService::getDateRange($key);
            $items[$key]['overview'] = $this->getRecordOverviewByDate($date);
            $items[$key]['key'] = $key;
            $items[$key]['text'] = $item;
        }

        return $items;
    }

    public function getRecordOverviewByDate(array $date): array
    {
        $conditions = [];
        if (count($date) == 2) {
            $conditions = ['between', 'date', $date[0], $date[1]];
        }
        $userId = \Yii::$app->user->id;
        $types = [TransactionType::EXPENSE, TransactionType::INCOME];
        $baseConditions = ['user_id' => $userId, 'transaction_type' => $types, 'exclude_from_stats' => false];
        $sum = Record::find()
            ->where($baseConditions)
            ->andWhere(['direction' => DirectionType::INCOME])
            ->andWhere($conditions)
            ->sum('amount_cent');
        $items['income'] = $sum ? (float)Setup::toYuan($sum) : 0;

        $sum = Record::find()
            ->where($baseConditions)
            ->andWhere(['direction' => DirectionType::EXPENSE])
            ->andWhere($conditions)
            ->sum('amount_cent');
        $items['expense'] = $sum ? (float)Setup::toYuan($sum) : 0;

        $items['surplus'] = (float)bcsub($items['income'], $items['expense'], 2);

        return $items;
    }

    public function getCategoryStatisticalData(array $date, int $transactionType)
    {
        $conditions = [];
        $items = [];
        if (count($date) == 2) {
            $conditions = ['between', 'date', $date[0], $date[1]];
        }
        $userId = \Yii::$app->user->id;
        $baseConditions = ['user_id' => $userId, 'transaction_type' => $transactionType];
        $categories = Category::find()->where($baseConditions)->asArray()->all();

        foreach ($categories as $key => $category) {
            $items[$key]['x'] = $category['name'];
            $sum = Record::find()
                ->where($baseConditions)
                ->andWhere(['category_id' => $category['id'], 'exclude_from_stats' => false])
                ->andWhere($conditions)
                ->sum('amount_cent');
            $items[$key]['y'] = $sum ? (float)Setup::toYuan($sum) : 0;
        }

        return $items;
    }

    /**
     * @param string $dateStr
     * @param int $transactionType
     * @return array
     * @throws InvalidConfigException
     */
    public function getRecordStatisticalData(string $dateStr, int $transactionType)
    {
        $dates = AnalysisDateType::getEveryDayByMonth($dateStr);
        $userId = \Yii::$app->user->id;
        $baseConditions = ['user_id' => $userId, 'transaction_type' => $transactionType, 'exclude_from_stats' => false];
        $items = [];
        foreach ($dates as $key => $date) {
            $items[$key]['x'] = sprintf("%02d", $key + 1);
            $sum = Record::find()
                ->where($baseConditions)
                ->andWhere(['between', 'date', $date[0], $date[1]])
                ->sum('amount_cent');
            $items[$key]['y'] = $sum ? (float)Setup::toYuan($sum) : 0;
        }
        return $items;
    }


    /**
     * @param $key
     * @return array
     * @throws \Exception
     */
    public static function getDateRange($key): array
    {
        $formatter = Yii::$app->formatter;
        $date = [];
        switch ($key) {
            case AnalysisDateType::TODAY:
                $date = [DateHelper::beginTimestamp(), DateHelper::endTimestamp()];
                break;
            case AnalysisDateType::YESTERDAY:
                $time = strtotime('-1 day');
                $date = [DateHelper::beginTimestamp($time), DateHelper::endTimestamp($time)];
                break;
            case AnalysisDateType::LAST_MONTH:
                $beginTime = $formatter->asDatetime(strtotime('-1 month'), 'php:01-m-Y');
                $endTime = $formatter->asDatetime('now', 'php:01-m-Y');
                $date = [DateHelper::beginTimestamp($beginTime), DateHelper::endTimestamp($endTime) - 3600 * 24];
                break;
            case AnalysisDateType::CURRENT_MONTH:
                $time = $formatter->asDatetime('now', 'php:01-m-Y');
                $date = [DateHelper::beginTimestamp($time), DateHelper::endTimestamp()];
                break;
        }

        return array_map(function ($i) use ($formatter) {
            return $formatter->asDatetime($i);
        }, $date);
    }

    /**
     * @param array $params
     * @return array
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function byCategory(array $params)
    {
        $items = [];
        $categories = Category::find()->where(['user_id' => Yii::$app->user->id])->asArray()->all();
        $categoriesMap = ArrayHelper::map($categories, 'id', 'name');
        $recordTableName = Record::tableName();
        foreach ([TransactionType::EXPENSE, TransactionType::INCOME] as $type) {
            $data = $this->getBaseQuery($params)
                ->select([
                    "{$recordTableName}.category_id",
                    "SUM({$recordTableName}.currency_amount_cent) AS currency_amount_cent"
                ])
                ->andWhere(['transaction_type' => $type])
                ->groupBy("{$recordTableName}.category_id")
                ->asArray()
                ->all();
            $k = TransactionType::getName($type);
            $items['total'][$k] = 0;
            $items[$k] = [];
            foreach ($data as $key => $value) {
                $v['category_name'] = data_get($categoriesMap, $value['category_id'], 0);
                $v['currency_amount'] = (float)Setup::toYuan($value['currency_amount_cent']);
                $items['total'][$k] += $v['currency_amount'];
                $items[$k][] = $v;
            }
        }
        $items['total']['surplus'] = (float)bcsub(
            data_get($items['total'], TransactionType::getName(TransactionType::INCOME), 0),
            data_get($items['total'], TransactionType::getName(TransactionType::EXPENSE), 0),
            2
        );

        return $items;
    }

    /**
     * @param array $params
     * @param string $format
     * @return array
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function byDate(array $params, string $format)
    {
        $items = [];
        $recordTableName = Record::tableName();
        foreach ([TransactionType::EXPENSE, TransactionType::INCOME] as $type) {
            $data = $this->getBaseQuery($params)
                ->select([
                    "DATE_FORMAT({$recordTableName}.date, '{$format}') as date",
                    "SUM({$recordTableName}.currency_amount_cent) AS currency_amount_cent"
                ])
                ->andWhere(['transaction_type' => $type])
                ->groupBy('date')
                ->asArray()
                ->all();

            $k = TransactionType::getName($type);
            $items['total'][$k] = 0;
            $items[$k] = [];
            foreach ($data as $key => $value) {
                $v['date'] = $value['date'];
                $v['currency_amount'] = (float)Setup::toYuan($value['currency_amount_cent']);
                $items['total'][$k] += $v['currency_amount'];
                $items[$k][] = $v;
            }
        }
        $items['total']['surplus'] = (float)bcsub(
            data_get($items['total'], TransactionType::getName(TransactionType::INCOME), 0),
            data_get($items['total'], TransactionType::getName(TransactionType::EXPENSE), 0),
            2
        );

        return $items;
    }


    /**
     * @param array $params
     * @return \yii\db\ActiveQuery
     * @throws \Exception
     */
    protected function getBaseQuery(array $params)
    {
        $baseConditions = ['user_id' => Yii::$app->user->id,];
        $condition = ['category_id' => request('category_id'), 'type' => request('transaction_type')];
        $query = Transaction::find()->where($baseConditions)->andFilterWhere($condition);
        if (isset($params['keyword']) && $searchKeywords = trim($params['keyword'])) {
            $query->andWhere(
                "MATCH(`description`, `tags`, `remark`) AGAINST ('*$searchKeywords*' IN BOOLEAN MODE)"
            );
        }
        if (($date = explode('~', data_get($params, 'date'))) && count($date) == 2) {
            $start = $date[0] . ' 00:00:00';
            $end = $date[1] . ' 23:59:59';
            $query->andWhere(['between', 'date', $start, $end]);
        }
        $transactionIds = $query->column();

        return Record::find()
            ->where($baseConditions)
            ->andWhere([
                'transaction_id' => $transactionIds,
                'exclude_from_stats' => (int)false,
            ])
            ->andFilterWhere([
                'account_id' => request('account_id'),
                'source' => request('source'),
            ]);
    }
}
