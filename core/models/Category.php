<?php

namespace app\core\models;

use app\core\exceptions\InvalidArgumentException;
use app\core\types\ColorType;
use app\core\types\TransactionType;
use Yii;
use yii\behaviors\TimestampBehavior;
use yiier\helpers\DateHelper;

/**
 * This is the model class for table "{{%category}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $transaction_type
 * @property string $name
 * @property string $color
 * @property string $icon_name
 * @property int|null $status
 * @property int $default
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Category extends \yii\db\ActiveRecord
{
    public const NOT_DEFAULT = 0;
    public const DEFAULT = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%category}}';
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
            [['transaction_type', 'name', 'color', 'icon_name'], 'required'],
            [['user_id', 'status', 'default'], 'integer'],
            ['transaction_type', 'in', 'range' => TransactionType::names()],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'icon_name'], 'string', 'max' => 120],
            ['color', 'in', 'range' => ColorType::items()],
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
            'transaction_type' => Yii::t('app', 'Transaction Type'),
            'name' => Yii::t('app', 'Name'),
            'color' => Yii::t('app', 'Color'),
            'icon_name' => Yii::t('app', 'Icon Name'),
            'status' => Yii::t('app', 'Status'),
            'default' => Yii::t('app', 'Default'),
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
            $this->transaction_type = TransactionType::toEnumValue($this->transaction_type);
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

        $fields['transaction_type'] = function (self $model) {
            return TransactionType::getName($model->transaction_type);
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
