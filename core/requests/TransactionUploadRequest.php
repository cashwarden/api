<?php

namespace app\core\requests;

class TransactionUploadRequest extends \yii\base\Model
{
    public $file;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['file'], 'required'],
            [
                'file',
                'file',
                'skipOnEmpty' => false,
                'extensions' => 'csv',
                'checkExtensionByMimeType' => false,
                'maxSize' => 1 * 1024 * 1024
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'file' => t('app', 'file'),
        ];
    }
}
