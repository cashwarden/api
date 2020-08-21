<?php

namespace app\core\models;

use app\core\exceptions\InvalidArgumentException;
use app\core\services\TransactionService;
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
 * @property int|null $status
 * @property int|null $exclude_from_stats
 * @property int|null $credit_card_limit
 * @property int|null $credit_card_repayment_day
 * @property int|null $credit_card_billing_day
 * @property int $default
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 */
class Account extends \yii\db\ActiveRecord
{
    public const DEFAULT = 1;
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

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => date('Y-m-d H:i:s'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'name', 'type', 'currency_balance', 'currency_code'], 'required'],
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
                    'status',
                    'credit_card_limit',
                    'credit_card_repayment_day',
                    'credit_card_billing_day',
                    'default'
                ],
                'integer'
            ],
            [['name'], 'string', 'max' => 120],
            [['color'], 'string', 'max' => 7],
            ['type', 'in', 'range' => AccountType::names()],
            [['balance', 'currency_balance'], MoneyValidator::class], //todo message
            ['exclude_from_stats', 'boolean', 'trueValue' => true, 'falseValue' => false, 'strict' => true],
            ['currency_code', 'in', 'range' => CurrencyCode::getKeys()],
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
            $this->currency_balance_cent = Setup::toFen($this->currency_balance);
            if ($this->currency_code == user('base_currency_code')) {
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
    }

    /**
     * @return array
     */
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['balance_cent'], $fields['user_id']);

        $fields['type'] = function (self $model) {
            return AccountType::getName($model->type);
        };

        $fields['type_name'] = function (self $model) {
            return data_get(AccountType::texts(), $model->type);
        };

        $fields['balance'] = function (self $model) {
            return Setup::toYuan($model->balance_cent);
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
