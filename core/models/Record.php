<?php

namespace app\core\models;

use app\core\types\DirectionType;
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
 * @property int|null $from_account_id
 * @property int|null $to_account_id
 * @property int $category_id
 * @property int $direction
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
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property-read Category $category
 * @property-read Account $fromAccount
 * @property-read Account $toAccount
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
            [['category_id', 'direction', 'currency_code'], 'required'],
            [
                [
                    'user_id',
                    'from_account_id',
                    'to_account_id',
                    'category_id',
                    'amount_cent',
                    'currency_amount_cent',
                    'transaction_status',
                    'reimbursement_status',
                    'rating'
                ],
                'integer'
            ],
            [['description', 'remark'], 'trim'],
            ['direction', 'in', 'range' => DirectionType::names()],
            [['amount', 'currency_amount'], MoneyValidator::class], //todo message
            [['amount', 'currency_amount'], 'compare', 'compareValue' => 0, 'operator' => '>'],
            [['date', 'created_at', 'updated_at'], 'safe'],
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
            'from_account_id' => Yii::t('app', 'From Account ID'),
            'to_account_id' => Yii::t('app', 'To Account ID'),
            'category_id' => Yii::t('app', 'Category ID'),
            'direction' => Yii::t('app', 'Direction'),
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
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->user_id = Yii::$app->user->id;
            }
            $this->tags = $this->tags ? implode(',', $this->tags) : null;
            $this->direction = DirectionType::toEnumValue($this->direction);
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


    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    public function getFromAccount()
    {
        return $this->hasOne(Account::class, ['id' => 'from_account_id']);
    }

    public function getToAccount()
    {
        return $this->hasOne(Account::class, ['id' => 'to_account_id']);
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

        $fields['direction'] = function (self $model) {
            return data_get(DirectionType::names(), $model->direction);
        };
        $fields['tags'] = function (self $model) {
            return $model->tags ? explode(',', $model->tags) : [];
        };

        $fields['category'] = function (self $model) {
            return $model->category;
        };

        $fields['from_account'] = function (self $model) {
            return $model->fromAccount;
        };

        $fields['to_account'] = function (self $model) {
            return $model->toAccount;
        };

        $fields['reimbursement_status'] = function (self $model) {
            return (bool)$model->reimbursement_status;
        };

        $fields['transaction_status'] = function (self $model) {
            return (bool)$model->transaction_status;
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
