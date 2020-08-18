<?php

namespace app\core\models;

use app\core\exceptions\InvalidArgumentException;
use app\core\types\DirectionType;
use app\core\types\ReimbursementStatus;
use app\core\types\RuleStatus;
use app\core\types\TransactionStatus;
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
 * @property int|null $if_direction 0:any
 * @property int|null $then_direction
 * @property int|null $then_category_id
 * @property int|null $then_from_account_id
 * @property int|null $then_transaction_status
 * @property int|null $then_reimbursement_status
 * @property string|null|array $then_tags Multiple choice use,
 * @property int|null $status
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
     * @throws InvalidArgumentException
     */
    public function rules()
    {
        return [
            [['name', 'if_keywords'], 'required'],
            [['user_id', 'then_category_id', 'then_from_account_id', 'then_to_account_id'], 'integer'],
            ['status', 'in', 'range' => RuleStatus::names()],
            [
                'if_direction',
                'in',
                'range' => [
                    DirectionType::getName(DirectionType::ANY),
                    DirectionType::getName(DirectionType::IN),
                    DirectionType::getName(DirectionType::TRANSFER),
                    DirectionType::getName(DirectionType::OUT),
                ]
            ],
            [
                'then_direction',
                'in',
                'range' => [
                    DirectionType::getName(DirectionType::IN),
                    DirectionType::getName(DirectionType::TRANSFER),
                    DirectionType::getName(DirectionType::OUT),
                ]
            ],
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
            'if_direction' => Yii::t('app', 'If Direction'),
            'then_direction' => Yii::t('app', 'Then Direction'),
            'then_category_id' => Yii::t('app', 'Then Category ID'),
            'then_from_account_id' => Yii::t('app', 'Then From Account ID'),
            'then_transaction_status' => Yii::t('app', 'Then Transaction Status'),
            'then_reimbursement_status' => Yii::t('app', 'Then Reimbursement Status'),
            'then_tags' => Yii::t('app', 'Then Tags'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @throws InvalidArgumentException
     */
    public function afterFind()
    {
        parent::afterFind();
//        is_null($this->if_direction) ?: $this->if_direction = DirectionType::getName($this->if_direction);
//        is_null($this->then_direction) ?: $this->then_direction = DirectionType::getName($this->then_direction);
//        is_null($this->status) ?: $this->status = RuleStatus::getName($this->status);
//        is_null($this->then_transaction_status) ?:
//            $this->then_transaction_status = TransactionStatus::getName($this->then_transaction_status);
//        is_null($this->then_reimbursement_status) ?:
//            $this->then_reimbursement_status = ReimbursementStatus::getName($this->then_reimbursement_status);
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
            $this->if_direction = $this->if_direction ? DirectionType::toEnumValue($this->if_direction) : null;
            $this->then_direction = $this->then_direction ? DirectionType::toEnumValue($this->then_direction) : null;
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

        $fields['if_direction'] = function (self $model) {
            return $model->if_direction ? DirectionType::getName($model->if_direction) : null;
        };

        $fields['then_direction'] = function (self $model) {
            return $model->then_direction ? DirectionType::getName($model->then_direction) : null;
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
