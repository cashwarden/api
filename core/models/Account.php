<?php

namespace app\core\models;

use app\core\exceptions\InvalidArgumentException;
use app\core\services\AccountService;
use app\core\services\TransactionService;
use app\core\types\AccountStatus;
use app\core\types\AccountType;
use app\core\types\ColorType;
use app\core\types\CurrencyCode;
use Yii;
use yii\behaviors\TimestampBehavior;
use yiier\helpers\DateHelper;
use yiier\helpers\Setup;
use yiier\validators\MoneyValidator;

/**
 * This is the model class for table "{{%account}}".
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property int|string $type
 * @property string $color
 * @property int|null $balance_cent
 * @property int|null $currency_balance_cent
 * @property string $currency_code
 * @property int|string $status
 * @property int|null $exclude_from_stats
 * @property int|null $credit_card_limit
 * @property int|null $credit_card_repayment_day
 * @property int|null $credit_card_billing_day
 * @property int $default
 * @property int|null $sort
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property-read User $user
 *
 */
class Account extends \yii\db\ActiveRecord
{
    public const DEFAULT = 1;
    public const NO_DEFAULT = 0;
    public $balance;
    public $currency_balance;

    public const SCENARIO_CREDIT_CARD = 'credit_card';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%account}}';
    }

    public function transactions()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_INSERT | self::OP_UPDATE | self::OP_DELETE,
            self::SCENARIO_CREDIT_CARD => self::OP_INSERT | self::OP_UPDATE | self::OP_DELETE,
        ];
    }

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => Yii::$app->formatter->asDatetime('now')
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'name', 'type', 'currency_balance', 'currency_code', 'default'], 'required'],
            [
                ['credit_card_limit', 'credit_card_repayment_day', 'credit_card_billing_day'],
                'required',
                'on' => self::SCENARIO_CREDIT_CARD
            ],
            [
                [
                    'user_id',
                    'balance_cent',
                    'currency_balance_cent',
                    'credit_card_limit',
                    'credit_card_repayment_day',
                    'credit_card_billing_day',
                    'sort',
                ],
                'integer'
            ],
            ['status', 'in', 'range' => AccountStatus::names()],
            [['name'], 'string', 'max' => 120],
            [['color'], 'string', 'max' => 7],
            ['type', 'in', 'range' => AccountType::names()],
            [['balance', 'currency_balance'], MoneyValidator::class, 'allowsNegative' => true], //todo message
            ['exclude_from_stats', 'boolean', 'trueValue' => true, 'falseValue' => false, 'strict' => true],
            ['default', 'boolean', 'trueValue' => true, 'falseValue' => false, 'strict' => true],
            ['currency_code', 'in', 'range' => CurrencyCode::getKeys()],
            [
                'name',
                'unique',
                'targetAttribute' => ['user_id', 'name'],
                'message' => Yii::t('app', 'The {attribute} has been used.')
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'name' => Yii::t('app', 'Name'),
            'type' => Yii::t('app', 'Type'),
            'color' => Yii::t('app', 'Color'),
            'balance' => Yii::t('app', 'Balance'),
            'balance_cent' => Yii::t('app', 'Balance Cent'),
            'currency_code' => Yii::t('app', 'Currency Code'),
            'status' => Yii::t('app', 'Status'),
            'exclude_from_stats' => Yii::t('app', 'Exclude From Stats'),
            'credit_card_limit' => Yii::t('app', 'Credit Card Limit'),
            'credit_card_repayment_day' => Yii::t('app', 'Credit Card Repayment Day'),
            'credit_card_billing_day' => Yii::t('app', 'Credit Card Billing Day'),
            'default' => Yii::t('app', 'Default'),
            'sort' => Yii::t('app', 'Sort'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     */
    public function afterFind()
    {
        parent::afterFind();
    }


    /**
     * @param bool $insert
     * @return bool
     * @throws InvalidArgumentException|\Throwable
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $ran = ColorType::items();
                $this->color = $this->color ?: $ran[mt_rand(0, count($ran) - 1)];
            }
            $this->status = is_null($this->status) ? AccountStatus::ACTIVE : AccountStatus::toEnumValue($this->status);
            $this->currency_balance_cent = Setup::toFen($this->currency_balance);
            if ($this->currency_code == $this->user->base_currency_code) {
                $this->balance_cent = $this->currency_balance_cent;
            } else {
                // $this->balance_cent = $this->currency_balance_cent;
                // todo 计算汇率
            }

            $this->type = AccountType::toEnumValue($this->type);
            return true;
        } else {
            return false;
        }
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if (data_get($changedAttributes, 'currency_balance_cent') !== $this->currency_balance_cent) {
            TransactionService::createAdjustRecord($this);
        }
        if ($this->default) {
            Account::updateAll(
                ['default' => self::NO_DEFAULT, 'updated_at' => Yii::$app->formatter->asDatetime('now')],
                ['and', ['user_id' => $this->user_id, 'default' => self::DEFAULT], ['!=', 'id', $this->id]]
            );
        }
    }

    public function afterDelete()
    {
        parent::afterDelete();
        AccountService::afterDelete($this);
    }

    /**
     * @return array
     */
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['currency_balance_cent'], $fields['balance_cent'], $fields['user_id']);

        $fields['type'] = function (self $model) {
            return AccountType::getName($model->type);
        };

        $fields['status'] = function (self $model) {
            return AccountStatus::getName($model->status);
        };

        $fields['icon_name'] = function (self $model) {
            return AccountType::getName($model->type);
        };

        $fields['type_name'] = function (self $model) {
            return data_get(AccountType::texts(), $model->type);
        };

        $fields['balance'] = function (self $model) {
            return Setup::toYuan($model->balance_cent);
        };

        $fields['currency_balance'] = function (self $model) {
            return Setup::toYuan($model->currency_balance_cent);
        };

        $fields['default'] = function (self $model) {
            return (bool)$model->default;
        };

        $fields['exclude_from_stats'] = function (self $model) {
            return (bool)$model->exclude_from_stats;
        };

        $fields['created_at'] = function (self $model) {
            return DateHelper::datetimeToIso8601($model->created_at);
        };

        $fields['updated_at'] = function (self $model) {
            return DateHelper::datetimeToIso8601($model->updated_at);
        };

        return $fields;
    }
}
