<?php

namespace app\core\models;

use app\core\exceptions\CannotOperateException;
use app\core\services\AccountService;
use app\core\services\RecurrenceService;
use app\core\types\DirectionType;
use app\core\types\RecordSource;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yiier\helpers\DateHelper;
use yiier\helpers\Setup;

/**
 * This is the model class for table "{{%record}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $account_id
 * @property int $transaction_type
 * @property int $category_id
 * @property int $amount_cent
 * @property int $currency_amount_cent
 * @property string $currency_code
 * @property int|null $transaction_id
 * @property int $direction
 * @property string $date
 * @property int $source
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property float $amount
 * @property-read Account $account
 * @property-read Category $category
 * @property-read Transaction $transaction
 */
class Record extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%record}}';
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


    public function transactions()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_INSERT | self::OP_UPDATE | self::OP_DELETE,
        ];
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [
                [
                    'user_id',
                    'account_id',
                    'transaction_type',
                    'category_id',
                    'currency_amount_cent',
                    'currency_code',
                    'direction',
                ],
                'required'
            ],
            [
                [
                    'user_id',
                    'account_id',
                    'transaction_type',
                    'category_id',
                    'amount_cent',
                    'currency_amount_cent',
                    'transaction_id',
                    'direction'
                ],
                'integer'
            ],
            ['direction', 'in', 'range' => [DirectionType::INCOME, DirectionType::EXPENSE]],
            ['source', 'in', 'range' => array_keys(RecordSource::names())],
            [['date'], 'datetime', 'format' => 'php:Y-m-d H:i'],
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
            'account_id' => Yii::t('app', 'Account ID'),
            'transaction_type' => Yii::t('app', 'Transaction Type'),
            'category_id' => Yii::t('app', 'Category ID'),
            'amount_cent' => Yii::t('app', 'Amount Cent'),
            'currency_amount_cent' => Yii::t('app', 'Currency Amount Cent'),
            'currency_code' => Yii::t('app', 'Currency Code'),
            'transaction_id' => Yii::t('app', 'Transaction ID'),
            'direction' => Yii::t('app', 'Direction'),
            'date' => Yii::t('app', 'Date'),
            'source' => Yii::t('app', 'Source'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }


    public function getTransaction()
    {
        return $this->hasOne(Transaction::class, ['id' => 'transaction_id']);
    }

    public function getAccount()
    {
        return $this->hasOne(Account::class, ['id' => 'account_id']);
    }

    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    /**
     * @param bool $insert
     * @return bool
     * @throws \Throwable
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->source = $this->source ?: RecordSource::WEB;
            if (!$this->amount_cent) {
                if ($this->currency_code == user('base_currency_code')) {
                    $this->amount_cent = $this->currency_amount_cent;
                } else {
                    // $this->amount_cent = $this->currency_amount_cent;
                    // todo 计算汇率
                }
            }
            return true;
        } else {
            return false;
        }
    }


    /**
     * @param bool $insert
     * @param array $changedAttributes
     * @throws \yii\db\Exception|\Throwable
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($this->transaction_id) {
            // Exclude balance adjustment transaction type
            AccountService::updateAccountBalance($this->account_id);
            $accountId = data_get($changedAttributes, 'account_id');
            if ($accountId && $accountId !== $this->account_id) {
                AccountService::updateAccountBalance($accountId);
            }
        }
    }

    /**
     * @return bool
     * @throws CannotOperateException
     */
    public function beforeDelete()
    {
        if (RecurrenceService::countByTransactionId($this->transaction_id, $this->user_id)) {
            throw new CannotOperateException(Yii::t('app', 'Cannot be deleted because it has been used.'));
        }
        return parent::beforeDelete();
    }

    /**
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \yii\db\StaleObjectException
     */
    public function afterDelete()
    {
        parent::afterDelete();
        if ($this->transaction_id) {
            if ($transaction = Transaction::find()->where(['id' => $this->transaction_id])->one()) {
                $transaction->delete();
            }
            $record = Record::find()
                ->where(['user_id' => $this->user_id, 'transaction_id' => $this->transaction_id])
                ->one();
            if ($record) {
                $record->delete();
            }
        }
        $this->account_id ? AccountService::updateAccountBalance($this->account_id) : null;
    }

    /**
     * @return array
     */
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['currency_amount_cent'], $fields['user_id'], $fields['amount_cent']);


        $fields['direction'] = function (self $model) {
            return DirectionType::getName($model->direction);
        };

        $fields['currency_amount'] = function (self $model) {
            return Setup::toYuan($model->currency_amount_cent);
        };

        $fields['amount'] = function (self $model) {
            return Setup::toYuan($model->amount_cent);
        };

        $fields['transaction'] = function (self $model) {
            return $model->transaction;
        };

        $fields['category'] = function (self $model) {
            return $model->category;
        };

        $fields['source_text'] = function (self $model) {
            return RecordSource::getName($model->source);
        };

        $fields['account'] = function (self $model) {
            return $model->account;
        };

        $fields['date'] = function (self $model) {
            return DateHelper::datetimeToIso8601($model->date);
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
