<?php

namespace app\core\requests;

class TransactionCreateByDescRequest extends \yii\base\Model
{
    public $description;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['description'], 'required'],
            [['description'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'description' => t('app', 'Description'),
        ];
    }
}
