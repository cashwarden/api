<?php

namespace app\core\models;

use Yii;

/**
 * This is the model class for table "{{%account}}".
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property int|null $type
 * @property string $color
 * @property int|null $balance_cent
 * @property string $currency_code
 * @property int|null $status
 * @property int|null $exclude_from_stats
 * @property int|null $credit_card_limit
 * @property int|null $credit_card_repayment_day
 * @property int|null $credit_card_billing_day
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Account extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%account}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'name', 'color', 'currency_code'], 'required'],
            [
                [
                    'user_id',
                    'type',
                    'balance_cent',
                    'status',
                    'exclude_from_stats',
                    'credit_card_limit',
                    'credit_card_repayment_day',
                    'credit_card_billing_day'
                ],
                'integer'
            ],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 120],
            [['color'], 'string', 'max' => 7],
            [['currency_code'], 'string', 'max' => 3],
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
            'type' => Yii::t('app', 'Type'),
            'color' => Yii::t('app', 'Color'),
            'balance_cent' => Yii::t('app', 'Balance Cent'),
            'currency_code' => Yii::t('app', 'Currency Code'),
            'status' => Yii::t('app', 'Status'),
            'exclude_from_stats' => Yii::t('app', 'Exclude From Stats'),
            'credit_card_limit' => Yii::t('app', 'Credit Card Limit'),
            'credit_card_repayment_day' => Yii::t('app', 'Credit Card Repayment Day'),
            'credit_card_billing_day' => Yii::t('app', 'Credit Card Billing Day'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
