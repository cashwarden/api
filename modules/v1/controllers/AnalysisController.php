<?php

namespace app\modules\v1\controllers;

use app\core\exceptions\InvalidArgumentException;
use app\core\traits\ServiceTrait;
use app\core\types\AnalysisGroupDateType;

class AnalysisController extends ActiveController
{
    use ServiceTrait;

    public $modelClass = '';
    public $noAuthActions = [];

    public function actions()
    {
        $actions = parent::actions();
        // 注销系统自带的实现方法
        unset($actions['update'], $actions['index'], $actions['delete'], $actions['create']);
        return $actions;
    }

    /**
     * @return array
     * @throws InvalidArgumentException
     */
    public function actionCategory()
    {
        $params = \Yii::$app->request->queryParams;
        return $this->analysisService->byCategory($params);
    }

    /**
     * @return array
     * @throws InvalidArgumentException
     */
    public function actionDate()
    {
        $params = \Yii::$app->request->queryParams;
        $groupByDateType = request('group_type') ?: AnalysisGroupDateType::DAY;
        return $this->analysisService->byDate($params, AnalysisGroupDateType::getValue($groupByDateType));
    }
}
