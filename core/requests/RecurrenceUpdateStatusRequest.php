<?php

namespace app\core\requests;

use app\core\types\RecurrenceStatus;

class RecurrenceUpdateStatusRequest extends \yii\base\Model
{
    public $status;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status'], 'required'],
            ['status', 'in', 'range' => RecurrenceStatus::names()],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'status' => t('app', 'Status'),
        ];
    }
}
