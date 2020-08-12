<?php

namespace app\core\services;

use app\core\exceptions\InternalException;
use app\core\models\Account;
use app\core\models\Category;
use app\core\models\User;
use app\core\requests\JoinRequest;
use app\core\types\ColorType;
use app\core\types\DirectionType;
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
     * @throws InternalException|\Throwable
     */
    public function createUser(JoinRequest $request): User
    {
        $transaction = Yii::$app->db->beginTransaction();
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
            $this->createUserAfterInitData($user);
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
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


    /**
     * @param User $user
     * @throws DBException
     */
    public function createUserAfterInitData(User $user)
    {
        try {
            $account = new Account();
            $account->setAttributes([
                'name' => t('app', 'Cash'),
                'type' => 'cash',
                'user_id' => $user->id,
                'balance' => 0,
                'currency_code' => $user->base_currency_code
            ]);
            if (!$account->save()) {
                throw new DBException('Init Account fail ' . Setup::errorMessage($account->firstErrors));
            }
            $items = [
                [
                    'name' => t('app', 'Food and drink'),
                    'color' => ColorType::RED,
                    'icon_name' => 'utensils',
                    'direction' => DirectionType::OUT
                ],
                [
                    'name' => t('app', 'Home life'),
                    'color' => ColorType::ORANGE,
                    'icon_name' => 'home',
                    'direction' => DirectionType::OUT
                ],
                [
                    'name' => t('app', 'Traffic'),
                    'color' => ColorType::BLUE,
                    'icon_name' => 'car',
                    'direction' => DirectionType::OUT
                ],
                [
                    'name' => t('app', 'Recreation'),
                    'color' => ColorType::VOLCANO,
                    'icon_name' => 'gamepad',
                    'direction' => DirectionType::OUT
                ],
                [
                    'name' => t('app', 'Health care'),
                    'color' => ColorType::GREEN,
                    'icon_name' => 'briefcase-medical',
                    'direction' => DirectionType::OUT
                ],
                [
                    'name' => t('app', 'Clothes'),
                    'color' => ColorType::PURPLE,
                    'icon_name' => 'tshirt',
                    'direction' => DirectionType::OUT
                ],
                [
                    'name' => t('app', 'Cultural education'),
                    'color' => ColorType::CYAN,
                    'icon_name' => 'book',
                    'direction' => DirectionType::OUT
                ],
                [
                    'name' => t('app', 'Investment expenditure'),
                    'color' => ColorType::GOLD,
                    'icon_name' => 'seedling',
                    'direction' => DirectionType::OUT
                ],
                [
                    'name' => t('app', 'Childcare'),
                    'color' => ColorType::LIME,
                    'icon_name' => 'baby',
                    'direction' => DirectionType::OUT
                ],
                [
                    'name' => t('app', 'Other expenses'),
                    'color' => ColorType::GEEK_BLUE,
                    'icon_name' => 'wallet',
                    'direction' => DirectionType::OUT
                ],
                [
                    'name' => t('app', 'Work income'),
                    'color' => ColorType::BLUE,
                    'icon_name' => 'file-invoice-dollar',
                    'direction' => DirectionType::IN
                ],
                [
                    'name' => t('app', 'Investment income'),
                    'color' => ColorType::GOLD,
                    'icon_name' => 'chart-line',
                    'direction' => DirectionType::IN
                ],
                [
                    'name' => t('app', 'Other income'),
                    'color' => ColorType::MAGENTA,
                    'icon_name' => 'piggy-bank',
                    'direction' => DirectionType::IN
                ],
            ];
            $time = date('Y-m-d H:i:s');
            foreach ($items as $key => $value) {
                $rows[$key] = $value;
                $rows[$key]['user_id'] = $user->id;
                $rows[$key]['created_at'] = $time;
                $rows[$key]['updated_at'] = $time;
            }
            if (!ModelHelper::saveAll(Category::tableName(), $rows)) {
                throw new DBException('Init Category fail');
            }
        } catch (Exception $e) {
            throw $e;
        }
    }
}
