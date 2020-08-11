<?php

namespace app\core\services;

use app\core\exceptions\InternalException;
use app\core\models\Account;
use app\core\models\Category;
use app\core\models\User;
use app\core\requests\JoinRequest;
use app\core\types\UserStatus;
use Exception;
use sizeg\jwt\Jwt;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Exception as DBException;
use yiier\helpers\ModelHelper;
use yiier\helpers\Setup;

class UserService
{
    /**
     * @param JoinRequest $request
     * @return User
     * @throws InternalException
     */
    public function createUser(JoinRequest $request): User
    {
        $user = new User();
        try {
            $user->username = $request->username;
            $user->email = $request->email;
            $user->base_currency_code = $request->base_currency_code;
            $user->setPassword($request->password);
            $user->generateAuthKey();
            if (!$user->save()) {
                throw new DBException(Setup::errorMessage($user->firstErrors));
            }
        } catch (Exception $e) {
            Yii::error(
                ['request_id' => Yii::$app->requestId->id, $user->attributes, $user->errors, (string)$e],
                __FUNCTION__
            );
            throw new InternalException($e->getMessage());
        }
        return $user;
    }


    /**
     * @return string
     * @throws \Throwable
     */
    public function getToken(): string
    {
        /** @var Jwt $jwt */
        $jwt = Yii::$app->jwt;
        if (!$jwt->key) {
            throw new InternalException(t('app', 'The JWT secret must be configured first.'));
        }
        $signer = $jwt->getSigner('HS256');
        $key = $jwt->getKey();
        $time = time();
        return (string)$jwt->getBuilder()
            ->issuedBy(params('appUrl'))
            ->identifiedBy(Yii::$app->name, true)
            ->issuedAt($time)
            ->expiresAt($time + 3600 * 72)
            ->withClaim('username', \user('username'))
            ->withClaim('id', \user('id'))
            ->getToken($signer, $key);
    }


    /**
     * @param string $value
     * @return User|ActiveRecord|null
     */
    public static function getUserByUsernameOrEmail(string $value)
    {
        $condition = strpos($value, '@') ? ['email' => $value] : ['username' => $value];
        return User::find()->where(['status' => UserStatus::ACTIVE])
            ->andWhere($condition)
            ->one();
    }


    public function createUserAfterInitData()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $account = new Account();
            $account->setAttributes([
                'name' => t('app', 'Cash'),
                'type' => 'cash',
                'currency_code' => \user('base_currency_code')
            ]);
            if (!$account->save()) {
                throw new DBException('Init Account fail' . Setup::errorMessage($account->firstErrors));
            }
            $items = [
                t('app', 'Food and drink'),
                t('app', 'Home life'),
                t('app', 'Traffic'),
                t('app', 'Recreation'),
                t('app', 'Health care'),
                t('app', 'Clothes'),
                t('app', 'Cultural education'),
                t('app', 'Investment expenditure'),
                t('app', 'Childcare'),
                t('app', 'Other expenses'),
            ];
            foreach ($items as $key => $value) {
                $rows[$key]['title'] = $value['title'];
                $rows[$key]['user_id'] = \user('id');
            }
            if (!ModelHelper::saveAll(Category::tableName(), $rows)) {
                throw new DBException('Init Category fail');
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}
