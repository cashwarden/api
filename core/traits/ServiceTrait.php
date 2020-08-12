<?php

/**
 * author     : forecho <caizhenghai@gmail.com>
 * createTime : 2019/5/12 4:58 PM
 * description:
 */

namespace app\core\traits;

use app\core\services\AccountService;
use app\core\services\CategoryService;
use app\core\services\RecordService;
use app\core\services\UserService;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Trait ServiceTrait
 * @property UserService $userService
 * @property AccountService $accountService
 * @property RecordService $recordService
 * @property CategoryService $categoryService
 */
trait ServiceTrait
{
    /**
     * @return UserService|object
     */
    public function getUserService()
    {
        try {
            return Yii::createObject(UserService::class);
        } catch (InvalidConfigException $e) {
            return new UserService();
        }
    }


    /**
     * @return AccountService|object
     */
    public function getAccountService()
    {
        try {
            return Yii::createObject(AccountService::class);
        } catch (InvalidConfigException $e) {
            return new AccountService();
        }
    }

    /**
     * @return RecordService|object
     */
    public function getRecordService()
    {
        try {
            return Yii::createObject(RecordService::class);
        } catch (InvalidConfigException $e) {
            return new RecordService();
        }
    }

    /**
     * @return CategoryService|object
     */
    public function getCategoryService()
    {
        try {
            return Yii::createObject(CategoryService::class);
        } catch (InvalidConfigException $e) {
            return new CategoryService();
        }
    }
}
