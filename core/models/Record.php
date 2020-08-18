<?php

namespace app\core\models;

use app\core\services\RecordService;
use app\core\types\DirectionType;
use app\core\types\ReimbursementStatus;
use app\core\types\TransactionStatus;
use app\core\types\TransactionType;
use Yii;
use yii\behaviors\TimestampBehavior;
use yiier\helpers\DateHelper;
use yiier\helpers\Setup;
use yiier\validators\ArrayValidator;
use yiier\validators\MoneyValidator;

/**
 * This is the model class for table "{{%record}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $account_id
 * @property int $category_id
 * @property int $direction
 * @property string $transaction_type
 * @property int $amount_cent
 * @property int $currency_amount_cent
 * @property string $currency_code
 * @property string|array $tags
 * @property string|null $description
 * @property string|null $remark
 * @property string|null $image
 * @property int|null $transaction_status
 * @property int|null $reimbursement_status
 * @property int|null $rating
 * @property string $date
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property-read Category $category
 * @property-read Account $account
 */
class Record extends \yii\db\ActiveRecord
{
    /**
     * @var integer
     */
    public $amount;

    /**
     * @var integer
     */
    public $currency_amount;
    /**
     * @var mixed|null
     */
    public $to_account_id;


    public function transactions()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_INSERT | self::OP_UPDATE | self::OP_DELETE
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%record}}';
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
            [['category_id', 'transaction_type', 'currency_code', 'account_id'], 'required'],
            [
                [
                    'user_id',
                    'category_id',
                    'amount_cent',
                    'currency_amount_cent',
                    'rating'
                ],
                'integer'
            ],
            [['description', 'remark'], 'trim'],
            ['transaction_type', 'in', 'range' => TransactionType::names()],
            ['reimbursement_status', 'in', 'range' => ReimbursementStatus::names()],
            ['transaction_status', 'in', 'range' => TransactionStatus::names()],
            ['direction', 'in', 'range' => DirectionType::names()],
            [['amount', 'currency_amount'], MoneyValidator::class], //todo message
            [['amount', 'currency_amount'], 'compare', 'compareValue' => 0, 'operator' => '>'],
            [['date'], 'safe'],
            [['currency_code'], 'string', 'max' => 3],
            [['description', 'remark', 'image'], 'string', 'max' => 255],
            ['tags', ArrayValidator::class],
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
            'category_id' => Yii::t('app', 'Category ID'),
            'direction' => Yii::t('app', 'Direction'),
            'transaction_type' => Yii::t('app', 'Transaction Type'),
            'amount_cent' => Yii::t('app', 'Amount Cent'),
            'amount' => Yii::t('app', 'Amount'),
            'currency_amount_cent' => Yii::t('app', 'Currency Amount Cent'),
            'currency_amount' => Yii::t('app', 'Currency Amount'),
            'currency_code' => Yii::t('app', 'Currency Code'),
            'tags' => Yii::t('app', 'Tags'),
            'description' => Yii::t('app', 'Description'),
            'remark' => Yii::t('app', 'Remark'),
            'image' => Yii::t('app', 'Image'),
            'transaction_status' => Yii::t('app', 'Transaction Status'),
            'reimbursement_status' => Yii::t('app', 'Reimbursement Status'),
            'rating' => Yii::t('app', 'Rating'),
            'date' => Yii::t('app', 'Date'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }


    /**
     * @param bool $insert
     * @return bool
     * @throws \Throwable
     * @throws \app\core\exceptions\InvalidArgumentException
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->user_id = Yii::$app->user->id;
            }
            $this->reimbursement_status = is_null($this->reimbursement_status) ?
                ReimbursementStatus::NONE : ReimbursementStatus::toEnumValue($this->reimbursement_status);
            $this->transaction_status = is_null($this->transaction_status) ?
                TransactionStatus::DONE : TransactionStatus::toEnumValue($this->transaction_status);

            $this->tags = $this->tags ? implode(',', $this->tags) : null;
            $this->transaction_type = TransactionType::toEnumValue($this->transaction_type);

            $this->direction = is_null($this->direction) ?
                $this->transaction_type : DirectionType::toEnumValue($this->direction);


            $this->amount_cent = Setup::toFen($this->amount);
            if ($this->currency_code == user('base_currency_code')) {
                $this->currency_amount_cent = $this->amount_cent;
            } else {
                // todo 计算汇率
            }
            // $this->currency_amount_cent = Setup::toFen($this->currency_amount);
            return true;
        } else {
            return false;
        }
    }


    /**
     * @param bool $insert
     * @param array $changedAttributes
     * @throws \app\core\exceptions\InternalException
     * @throws \yii\db\Exception
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($this->transaction_type == TransactionType::TRANSFER && $this->direction == DirectionType::OUT) {
            RecordService::transferInto($this, $this->to_account_id);
        }
    }


    public static function getDefaultDirection(int $transactionType): int
    {
        if ($transactionType == TransactionType::IN) {
            return DirectionType::IN;
        }
        if (in_array($transactionType, [TransactionType::OUT, TransactionType::TRANSFER])) {
            return DirectionType::OUT;
        }
        if ($transactionType == TransactionType::ADJUST) {
            // 调整余额
            return DirectionType::OUT;
        }
    }


    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    public function getAccount()
    {
        return $this->hasOne(Account::class, ['id' => 'account_id']);
    }

    /**
     * @return array
     */
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['currency_amount_cent'], $fields['user_id'], $fields['amount_cent']);

        $fields['currency_amount'] = function (self $model) {
            return Setup::toYuan($model->currency_amount_cent);
        };

        $fields['amount'] = function (self $model) {
            return Setup::toYuan($model->amount_cent);
        };

        $fields['transaction_type'] = function (self $model) {
            return TransactionType::getName($model->transaction_type);
        };

        $fields['direction'] = function (self $model) {
            return data_get(DirectionType::names(), $model->direction);
        };

        $fields['tags'] = function (self $model) {
            return $model->tags ? explode(',', $model->tags) : [];
        };

        $fields['category'] = function (self $model) {
            return $model->category;
        };

        $fields['account'] = function (self $model) {
            return $model->account;
        };

        $fields['reimbursement_status'] = function (self $model) {
            return ReimbursementStatus::getName($model->reimbursement_status);
        };

        $fields['transaction_status'] = function (self $model) {
            return TransactionStatus::getName($model->transaction_status);
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
