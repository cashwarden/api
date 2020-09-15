<?php

namespace app\core\requests;

use app\core\models\User;
use app\core\types\CurrencyCode;

class JoinRequest extends \yii\base\Model
{
    public $username;
    public $email;
    public $password;
    public $base_currency_code;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'email'], 'trim'],
            [['username', 'email', 'base_currency_code'], 'required'],

            ['username', 'unique', 'targetClass' => User::class],
            ['username', 'string', 'min' => 3, 'max' => 60],

            ['email', 'string', 'min' => 2, 'max' => 120],
            ['email', 'unique', 'targetClass' => User::class],
            ['email', 'email'],

            ['password', 'required'],
            ['password', 'string', 'min' => 6],

            ['base_currency_code', 'in', 'range' => CurrencyCode::getKeys()],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username' => t('app', 'Username'),
            'password' => t('app', 'Password'),
            'email' => t('app', 'Email'),
            'base_currency_code' => t('app', 'Base Currency Code'),
        ];
    }
}
