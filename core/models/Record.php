<?php

namespace app\core\models;

use app\core\types\DirectionType;
use Yii;
use yii\behaviors\TimestampBehavior;
use yiier\helpers\DateHelper;
use yiier\helpers\Setup;
use yiier\validators\MoneyValidator;

/**
 * This is the model class for table "{{%record}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $account_id
 * @property int|null $target_account_id
 * @property int $action_type
 * @property int $category_id
 * @property int $direction
 * @property int $amount_cent
 * @property int $currency_amount_cent
 * @property string $currency_code
 * @property string|null $tags
 * @property string|null $description
 * @property string|null $remark
 * @property string|null $image
 * @property int|null $trading_status
 * @property int|null $reimbursement_status
 * @property int|null $rating
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
                    'action_type',
                    'category_id',
                    'direction',
                    'amount_cent',
                    'currency_amount_cent',
                    'currency_code'
                ],
                'required'
            ],
            [
                [
                    'user_id',
                    'account_id',
                    'target_account_id',
                    'action_type',
                    'category_id',
                    'direction',
                    'amount_cent',
                    'currency_amount_cent',
                    'trading_status',
                    'reimbursement_status',
                    'rating'
                ],
                'integer'
            ],
            [['amount', 'currency_amount'], MoneyValidator::class], //todo message
            [['created_at', 'updated_at'], 'safe'],
            [['currency_code'], 'string', 'max' => 3],
            [['tags', 'description', 'remark', 'image'], 'string', 'max' => 255],
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
            'target_account_id' => Yii::t('app', 'Target Account ID'),
            'action_type' => Yii::t('app', 'Action Type'),
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
            'trading_status' => Yii::t('app', 'Trading Status'),
            'reimbursement_status' => Yii::t('app', 'Reimbursement Status'),
            'rating' => Yii::t('app', 'Rating'),
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
            $this->amount_cent = Setup::toFen($this->amount);
            $this->currency_amount_cent = Setup::toFen($this->currency_amount);
            return true;
        } else {
            return false;
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

        $fields['account_name'] = function (self $model) {
            return data_get($model->account, 'name');
        };

        $fields['direction'] = function (self $model) {
            return data_get(DirectionType::names(), $model->direction);
        };

        $fields['category_name'] = function (self $model) {
            return data_get($model->category, 'name');
        };

        $fields['reimbursement_status'] = function (self $model) {
            return (bool)$model->reimbursement_status;
        };

        $fields['trading_status'] = function (self $model) {
            return (bool)$model->trading_status;
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
