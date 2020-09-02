<?php

namespace app\core\models;

use Yii;

/**
 * This is the model class for table "{{%currency_rate}}".
 *
 * @property int $id
 * @property int $user_id
 * @property string $currency_code
 * @property string $currency_name
 * @property int|null $rate
 * @property int|null $status
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class CurrencyRate extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%currency_rate}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'currency_code', 'currency_name'], 'required'],
            [['user_id', 'rate', 'status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['currency_code'], 'string', 'max' => 3],
            [['currency_name'], 'string', 'max' => 60],
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
            'currency_code' => Yii::t('app', 'Currency Code'),
            'currency_name' => Yii::t('app', 'Currency Name'),
            'rate' => Yii::t('app', 'Rate'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
