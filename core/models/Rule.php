<?php

namespace app\core\models;

use app\core\exceptions\InvalidArgumentException;
use app\core\types\DirectionType;
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
 * @property int|null $then_account_id
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
     */
    public function rules()
    {
        return [
            [['name', 'if_keywords'], 'required'],
            [
                [
                    'user_id',
                    'if_direction',
                    'then_direction',
                    'then_category_id',
                    'then_account_id',
                    'then_transaction_status',
                    'then_reimbursement_status',
                    'status'
                ],
                'integer'
            ],
            [['if_keywords', 'then_tags'], ArrayValidator::class],
            [['created_at', 'updated_at'], 'safe'],
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
            'then_account_id' => Yii::t('app', 'Then Account ID'),
            'then_transaction_status' => Yii::t('app', 'Then Transaction Status'),
            'then_reimbursement_status' => Yii::t('app', 'Then Reimbursement Status'),
            'then_tags' => Yii::t('app', 'Then Tags'),
            'status' => Yii::t('app', 'Status'),
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
            return data_get(DirectionType::names(), $model->if_direction);
        };

        $fields['then_direction'] = function (self $model) {
            return data_get(DirectionType::names(), $model->then_direction);
        };

        $fields['then_tags'] = function (self $model) {
            return $model->then_tags ? explode(',', $model->then_tags) : [];
        };

        $fields['if_keywords'] = function (self $model) {
            return explode(',', $model->if_keywords);
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
