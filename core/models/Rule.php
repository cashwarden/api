<?php

namespace app\core\models;

use app\core\exceptions\InvalidArgumentException;
use app\core\types\ReimbursementStatus;
use app\core\types\RuleStatus;
use app\core\types\TransactionStatus;
use app\core\types\TransactionType;
use Yii;
use yii\behaviors\TimestampBehavior;
use yiier\helpers\DateHelper;
use yiier\validators\ArrayValidator;

/**
 * This is the model class for table "{{%rule}}".
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string|array $if_keywords Multiple choice use,
 * @property int $then_transaction_type
 * @property int|null $then_category_id
 * @property int|null $then_from_account_id
 * @property int|null $then_to_account_id
 * @property int|null $then_transaction_status
 * @property int|null $then_reimbursement_status
 * @property string|null|array $then_tags Multiple choice use,
 * @property int|null $status
 * @property int|null $sort
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Rule extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%rule}}';
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
            [['name', 'if_keywords', 'then_transaction_type'], 'required'],
            [['user_id', 'then_category_id', 'then_from_account_id', 'then_to_account_id', 'sort'], 'integer'],
            ['status', 'in', 'range' => RuleStatus::names()],
            ['then_transaction_type', 'in', 'range' => TransactionType::names()],
            ['then_reimbursement_status', 'in', 'range' => ReimbursementStatus::names()],
            ['then_transaction_status', 'in', 'range' => TransactionStatus::names()],
            [['if_keywords', 'then_tags'], ArrayValidator::class],
            [['name'], 'string', 'max' => 255],
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
            'if_keywords' => Yii::t('app', 'If Keywords'),
            'then_transaction_type' => Yii::t('app', 'Then Transaction Type'),
            'then_category_id' => Yii::t('app', 'Then Category ID'),
            'then_from_account_id' => Yii::t('app', 'Then From Account ID'),
            'then_to_account_id' => Yii::t('app', 'Then To Account ID'),
            'then_transaction_status' => Yii::t('app', 'Then Transaction Status'),
            'then_reimbursement_status' => Yii::t('app', 'Then Reimbursement Status'),
            'then_tags' => Yii::t('app', 'Then Tags'),
            'status' => Yii::t('app', 'Status'),
            'sort' => Yii::t('app', 'Sort'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @param bool $insert
     * @return bool
     * @throws InvalidArgumentException
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->user_id = Yii::$app->user->id;
            }
            $this->then_reimbursement_status = is_null($this->then_reimbursement_status) ?
                ReimbursementStatus::NONE : ReimbursementStatus::toEnumValue($this->then_reimbursement_status);
            $this->then_transaction_status = is_null($this->then_transaction_status) ?
                TransactionStatus::DONE : TransactionStatus::toEnumValue($this->then_transaction_status);

            $this->status = is_null($this->status) ? RuleStatus::ACTIVE : RuleStatus::toEnumValue($this->status);
            $this->then_transaction_type = TransactionType::toEnumValue($this->then_transaction_type);
            $this->if_keywords = $this->if_keywords ? implode(',', $this->if_keywords) : null;
            $this->then_tags = $this->then_tags ? implode(',', $this->then_tags) : null;
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return array
     */
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['user_id']);

        $fields['then_transaction_type'] = function (self $model) {
            return TransactionType::getName($model->then_transaction_type);
        };

        $fields['then_transaction_type_text'] = function (self $model) {
            return data_get(TransactionType::texts(), $model->then_transaction_type);
        };

        $fields['then_tags'] = function (self $model) {
            return $model->then_tags ? explode(',', $model->then_tags) : [];
        };

        $fields['if_keywords'] = function (self $model) {
            return explode(',', $model->if_keywords);
        };

        $fields['status'] = function (self $model) {
            return RuleStatus::getName($model->status);
        };
        $fields['then_reimbursement_status'] = function (self $model) {
            return ReimbursementStatus::getName($model->then_reimbursement_status);
        };

        $fields['then_transaction_status'] = function (self $model) {
            return TransactionStatus::getName($model->then_transaction_status);
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
