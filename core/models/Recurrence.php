<?php

namespace app\core\models;

use app\core\exceptions\InvalidArgumentException;
use app\core\services\RecurrenceService;
use app\core\services\TransactionService;
use app\core\types\RecurrenceFrequency;
use app\core\types\RecurrenceStatus;
use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yiier\helpers\DateHelper;

/**
 * This is the model class for table "{{%recurrence}}".
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property int $frequency
 * @property int|null $interval
 * @property string|null $schedule
 * @property int $transaction_id
 * @property string|null $started_at
 * @property string|null $execution_date
 * @property int|null $status
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property-read Transaction $transaction
 */
class Recurrence extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%recurrence}}';
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
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
     * @return bool
     * @throws InvalidConfigException
     */
    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            $this->execution_date = $this->execution_date ?
                Yii::$app->formatter->asDatetime($this->execution_date, 'php:Y-m-d') : null;
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'frequency', 'transaction_id'], 'required'],
            [['user_id', 'interval', 'transaction_id'], 'integer'],
            ['frequency', 'in', 'range' => RecurrenceFrequency::names()],
            ['status', 'in', 'range' => RecurrenceStatus::names()],
            [['started_at', 'execution_date'], 'datetime', 'format' => 'php:Y-m-d'],
            [['name'], 'string', 'max' => 255],
            [
                'transaction_id',
                function ($attribute, $params, $validator) {
                    try {
                        TransactionService::findCurrentOne($this->$attribute);
                    } catch (\Exception $e) {
                        $this->addError(
                            $attribute,
                            Yii::t('app', 'The {attribute} not found.', ['attribute' => $attribute])
                        );
                        return null;
                    }
                }
            ],
            [
                'schedule',
                'required',
                'when' => function (self $model) {
                    return in_array(
                        RecurrenceFrequency::toEnumValue($model->frequency),
                        [RecurrenceFrequency::WEEK, RecurrenceFrequency::MONTH, RecurrenceFrequency::YEAR]
                    );
                }
            ],
            [
                'schedule',
                'integer',
                'min' => 1,
                'max' => 7,
                'when' => function (self $model) {
                    return RecurrenceFrequency::toEnumValue($model->frequency) === RecurrenceFrequency::WEEK;
                }
            ],
            [
                'schedule',
                'datetime',
                'format' => 'd',
                'when' => function (self $model) {
                    return RecurrenceFrequency::toEnumValue($model->frequency) === RecurrenceFrequency::MONTH;
                }
            ],
            [
                'schedule',
                'datetime',
                'format' => 'M-d',
                'when' => function (self $model) {
                    return RecurrenceFrequency::toEnumValue($model->frequency) === RecurrenceFrequency::YEAR;
                }
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
            'frequency' => Yii::t('app', 'Frequency'),
            'interval' => Yii::t('app', 'Interval'),
            'schedule' => Yii::t('app', 'Schedule'),
            'transaction_id' => Yii::t('app', 'Transaction ID'),
            'started_at' => Yii::t('app', 'Started At'),
            'execution_date' => Yii::t('app', 'Execution Date'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @param bool $insert
     * @return bool
     * @throws InvalidArgumentException|InvalidConfigException
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->user_id = Yii::$app->user->id;
            }
            $this->started_at = Yii::$app->formatter->asDatetime($this->started_at ?: 'now', 'php:Y-m-d');
            $this->frequency = RecurrenceFrequency::toEnumValue($this->frequency);
            $this->status = is_null($this->status) ?
                RecurrenceStatus::ACTIVE : RecurrenceStatus::toEnumValue($this->status);
            $this->execution_date = RecurrenceService::getExecutionDate($this);
            return true;
        } else {
            return false;
        }
    }

    public function getTransaction()
    {
        return $this->hasOne(Transaction::class, ['id' => 'transaction_id']);
    }


    /**
     * @return array
     */
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['user_id']);

        $fields['status'] = function (self $model) {
            return RecurrenceStatus::getName($model->status);
        };

        $fields['frequency'] = function (self $model) {
            return RecurrenceFrequency::getName($model->frequency);
        };

        $fields['frequency_text'] = function (self $model) {
            return data_get(RecurrenceFrequency::texts(), $model->frequency);
        };

        $fields['started_at'] = function (self $model) {
            return DateHelper::datetimeToIso8601($model->started_at);
        };

        $fields['execution_date'] = function (self $model) {
            return DateHelper::datetimeToIso8601($model->execution_date);
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
