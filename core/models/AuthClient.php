<?php

namespace app\core\models;

use app\core\types\AuthClientType;
use Yii;
use yii\behaviors\TimestampBehavior;
use yiier\helpers\DateHelper;

/**
 * This is the model class for table "{{%auth_client}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $type
 * @property string $client_id
 * @property string|null $client_username
 * @property string|null $data
 * @property int $status
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property-read User $user
 */
class AuthClient extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%auth_client}}';
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
            [['user_id', 'type', 'client_id', 'status'], 'required'],
            [['user_id', 'type', 'status'], 'integer'],
            [['data'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['client_id', 'client_username'], 'string', 'max' => 255],
            [['user_id', 'type'], 'unique', 'targetAttribute' => ['user_id', 'type']],
            [['type', 'client_id'], 'unique', 'targetAttribute' => ['type', 'client_id']],
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
            'type' => Yii::t('app', 'Type'),
            'client_id' => Yii::t('app', 'Client ID'),
            'client_username' => Yii::t('app', 'Client Username'),
            'data' => Yii::t('app', 'Data'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @return array
     */
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['status'], $fields['data'], $fields['user_id'], $fields['client_id']);

        $fields['type'] = function (self $model) {
            return AuthClientType::getName($model->type);
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
