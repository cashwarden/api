<?php

namespace app\modules\v1\controllers;

use app\core\models\Rule;
use app\core\traits\ServiceTrait;

/**
 * Rule controller for the `v1` module
 */
class RuleController extends ActiveController
{
    use ServiceTrait;

    public $modelClass = Rule::class;
}
