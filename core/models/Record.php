<?php

namespace app\core\models;

use app\core\types\DirectionType;
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
 * @property int $category_id
 * @property int $amount_cent
 * @property int $currency_amount_cent
 * @property string $currency_code
 * @property int|null $transaction_id
 * @property int $direction
 * @property string $date
 * @property string|null $created_at
 * @property string|null $updated_at
 *
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
            [
                [
                    'user_id',
                    'account_id',
                    'category_id',
                    'amount_cent',
                    'currency_amount_cent',
                    'currency_code',
                    'direction'
                ],
                'required'
            ],
            [
                [
                    'user_id',
                    'account_id',
                    'category_id',
                    'amount_cent',
                    'currency_amount_cent',
                    'transaction_id',
                    'direction'
                ],
                'integer'
            ],
            ['direction', 'in', 'range' => [DirectionType::OUT, DirectionType::IN]],
            [['date'], 'safe'],
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
            'amount_cent' => Yii::t('app', 'Amount Cent'),
            'currency_amount_cent' => Yii::t('app', 'Currency Amount Cent'),
            'currency_code' => Yii::t('app', 'Currency Code'),
            'transaction_id' => Yii::t('app', 'Transaction ID'),
            'direction' => Yii::t('app', 'Direction'),
            'date' => Yii::t('app', 'Date'),
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
     * @return array
     */
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['currency_amount_cent'], $fields['user_id'], $fields['amount_cent']);

        $fields['transaction'] = function (self $model) {
            return $model->transaction;
        };

        $fields['direction'] = function (self $model) {
            return DirectionType::getName($model->direction);
        };

        $fields['currency_amount'] = function (self $model) {
            return Setup::toYuan($model->currency_amount_cent);
        };

        $fields['amount'] = function (self $model) {
            return Setup::toYuan($model->amount_cent);
        };

        $fields['category'] = function (self $model) {
            return $model->category;
        };

        $fields['account'] = function (self $model) {
            return $model->account;
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
