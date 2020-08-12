<?php

namespace app\modules\v1\controllers;

use app\core\models\Category;

/**
 * Category controller for the `v1` module
 */
class CategoryController extends ActiveController
{
    public $modelClass = Category::class;
    public $noAuthActions = [];
}
