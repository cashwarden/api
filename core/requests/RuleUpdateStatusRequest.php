<?php

namespace app\core\requests;

use app\core\types\RuleStatus;

class RuleUpdateStatusRequest extends \yii\base\Model
{
    public $status;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status'], 'required'],
            ['status', 'in', 'range' => RuleStatus::names()],
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
