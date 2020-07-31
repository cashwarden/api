<?php

namespace app\core\models;

use Yii;

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
 * @property string|null $remark
 * @property string|null $image
 * @property int|null $trading_status
 * @property int|null $reimbursement_status
 * @property int|null $rating
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Record extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%record}}';
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
            [['created_at', 'updated_at'], 'safe'],
            [['currency_code'], 'string', 'max' => 3],
            [['tags', 'remark', 'image'], 'string', 'max' => 255],
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
            'currency_amount_cent' => Yii::t('app', 'Currency Amount Cent'),
            'currency_code' => Yii::t('app', 'Currency Code'),
            'tags' => Yii::t('app', 'Tags'),
            'remark' => Yii::t('app', 'Remark'),
            'image' => Yii::t('app', 'Image'),
            'trading_status' => Yii::t('app', 'Trading Status'),
            'reimbursement_status' => Yii::t('app', 'Reimbursement Status'),
            'rating' => Yii::t('app', 'Rating'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
