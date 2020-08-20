<?php

namespace app\modules\v1\controllers;

use app\core\models\Record;
use app\core\traits\ServiceTrait;

/**
 * Record controller for the `v1` module
 */
class RecordController extends ActiveController
{
    use ServiceTrait;

    public $modelClass = Record::class;
    public $noAuthActions = [];

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create'], $actions['update']);
        return $actions;
    }
}
