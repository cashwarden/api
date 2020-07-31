<?php

namespace app\core\models;

use Yii;

/**
 * This is the model class for table "{{%category}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $direction
 * @property string $name
 * @property string $color
 * @property string $icon_name
 * @property int|null $status
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Category extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%category}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'direction', 'name', 'color', 'icon_name'], 'required'],
            [['user_id', 'direction', 'status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'icon_name'], 'string', 'max' => 120],
            [['color'], 'string', 'max' => 7],
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
            'direction' => Yii::t('app', 'Direction'),
            'name' => Yii::t('app', 'Name'),
            'color' => Yii::t('app', 'Color'),
            'icon_name' => Yii::t('app', 'Icon Name'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
