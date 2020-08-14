<?php

namespace app\core\models;

use Yii;

/**
 * This is the model class for table "{{%tag}}".
 *
 * @property int $id
 * @property int $user_id
 * @property string $color
 * @property string $name
 * @property int|null $count
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Tag extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tag}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'color', 'name'], 'required'],
            [['user_id', 'count'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['color'], 'string', 'max' => 7],
            [['name'], 'string', 'max' => 60],
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
            'color' => Yii::t('app', 'Color'),
            'name' => Yii::t('app', 'Name'),
            'count' => Yii::t('app', 'Count'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
