<?php

namespace app\core\traits;

use yii\db\ActiveQuery;

trait FixDataTrait
{
    /**
     * @var integer 每次数量
     */
    protected $onceCount = 1000;

    /**
     * @var int （减轻DB负担）休眠，单位秒
     */
    protected $sleepSecond = 1;

    /**
     * 迁移数据库
     * @param ActiveQuery $query 查询 query
     * @param \Closure $existCallback 检查是否存在的回调函数
     * @param \Closure $callback 添加、迁移数据
     * @param bool $userTransaction
     * @return array 添加成功的 ID 数组
     * @throws \Exception
     */
    private function migrate($query, \Closure $existCallback, \Closure $callback, $userTransaction = true): array
    {
        $transaction = $userTransaction ? \Yii::$app->db->beginTransaction() : null;
        $count = $query->count();
        try {
            for ($i = 0; $i <= (int)ceil($count / $this->onceCount); $i++) {
                $items = $query
                    ->orderBy(['id' => SORT_ASC])
                    ->limit($this->onceCount)
                    ->offset($i * $this->onceCount)
                    ->all();
                // item is array
                foreach ($items as $item) {
                    if (!call_user_func($existCallback, $item)) {
                        // todo batch insert
                        $ids[] = call_user_func($callback, $item);
                    }
                }
            }
            $userTransaction ? $transaction->commit() : null;
            sleep($this->sleepSecond);
            return $ids ?? [];
        } catch (\Exception $e) {
            $userTransaction ? $transaction->rollBack() : null;
            \Yii::error('修复数据失败', ['query' => $query, (string)$e]);
            throw $e;
        }
    }
}
