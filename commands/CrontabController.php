<?php

namespace app\commands;

use app\core\traits\ServiceTrait;
use yii\console\Controller;

class CrontabController extends Controller
{
    use ServiceTrait;

    public function actionRecurrence()
    {
        $this->recurrenceService->createRecords();
    }
}
