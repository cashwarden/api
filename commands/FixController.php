<?php

namespace app\commands;

use app\core\models\Record;
use app\core\models\Transaction;
use app\core\traits\FixDataTrait;
use app\core\types\RecordSource;
use app\core\types\TransactionType;
use yii\console\Controller;

class FixController extends Controller
{
    use FixDataTrait;

    /**
     * @var int
     */
    private $count;

    /**
     * @throws \Exception
     */
    public function actionRecord()
    {
        $query = Record::find();
        $this->migrate(
            $query,
            function ($item) {
                return false;
            },
            function (Record $item) {
                $transaction = $item->transaction;
                $source = data_get($transaction, 'description') ? RecordSource::TELEGRAM : RecordSource::WEB;
                $transactionType = data_get($transaction, 'type') ?: TransactionType::ADJUST;

                $date = current(explode(' ', $item->date));
                try {
                    $this->count += Record::updateAll(
                        ['source' => $source, 'transaction_type' => $transactionType, 'date' => $date],
                        ['id' => $item->id]
                    );
                } catch (\Exception $e) {
                    $this->stdout("更新 {$item->id} 失败\n");
                }
            },
            false
        );

        $query = Transaction::find();
        $this->migrate(
            $query,
            function ($item) {
                return false;
            },
            function (Transaction $item) {
                try {
                    $this->count += Transaction::updateAll(
                        ['date' => current(explode(' ', $item->date))],
                        ['id' => $item->id]
                    );
                } catch (\Exception $e) {
                    $this->stdout("更新 {$item->id} 失败\n");
                }
            },
            false
        );
        $this->stdout("更新了 {$this->count} 条数据\n");
    }
}
