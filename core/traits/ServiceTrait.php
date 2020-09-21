<?php

/**
 * author     : forecho <caizhenghai@gmail.com>
 * createTime : 2019/5/12 4:58 PM
 * description:
 */

namespace app\core\traits;

use app\core\services\AccountService;
use app\core\services\AnalysisService;
use app\core\services\CategoryService;
use app\core\services\RecurrenceService;
use app\core\services\RuleService;
use app\core\services\TagService;
use app\core\services\TelegramService;
use app\core\services\TransactionService;
use app\core\services\UploadService;
use app\core\services\UserService;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Trait ServiceTrait
 * @property UserService $userService
 * @property AccountService $accountService
 * @property TransactionService $transactionService
 * @property CategoryService $categoryService
 * @property RuleService $ruleService
 * @property TelegramService $telegramService
 * @property AnalysisService $analysisService
 * @property TagService $tagService
 * @property RecurrenceService $recurrenceService
 * @property UploadService $uploadService
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
     * @return TransactionService|object
     */
    public function getTransactionService()
    {
        try {
            return Yii::createObject(TransactionService::class);
        } catch (InvalidConfigException $e) {
            return new TransactionService();
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

    /**
     * @return RuleService|object
     */
    public function getRuleService()
    {
        try {
            return Yii::createObject(RuleService::class);
        } catch (InvalidConfigException $e) {
            return new RuleService();
        }
    }


    /**
     * @return TelegramService|object
     */
    public function getTelegramService()
    {
        try {
            return Yii::createObject(TelegramService::class);
        } catch (InvalidConfigException $e) {
            return new TelegramService();
        }
    }

    /**
     * @return AnalysisService|object
     */
    public function getAnalysisService()
    {
        try {
            return Yii::createObject(AnalysisService::class);
        } catch (InvalidConfigException $e) {
            return new AnalysisService();
        }
    }

    /**
     * @return TagService|object
     */
    public function getTagService()
    {
        try {
            return Yii::createObject(TagService::class);
        } catch (InvalidConfigException $e) {
            return new TagService();
        }
    }

    /**
     * @return RecurrenceService|object
     */
    public function getRecurrenceService()
    {
        try {
            return Yii::createObject(RecurrenceService::class);
        } catch (InvalidConfigException $e) {
            return new RecurrenceService();
        }
    }

    /**
     * @return UploadService|object
     */
    public function getUploadService()
    {
        try {
            return Yii::createObject(UploadService::class);
        } catch (InvalidConfigException $e) {
            return new UploadService();
        }
    }
}
