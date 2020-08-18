<?php

namespace app\core\models;

use app\core\types\DirectionType;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%record}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $account_id
 * @property int $amount_cent
 * @property int $transaction_id
 * @property int $direction
 * @property string $date
 * @property string|null $created_at
 * @property string|null $updated_at
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
            [['user_id', 'account_id', 'amount_cent', 'transaction_id', 'direction'], 'required'],
            [['user_id', 'account_id', 'amount_cent', 'transaction_id', 'direction'], 'integer'],
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
            'amount_cent' => Yii::t('app', 'Amount Cent'),
            'transaction_id' => Yii::t('app', 'Transaction ID'),
            'direction' => Yii::t('app', 'Direction'),
            'date' => Yii::t('app', 'Date'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
