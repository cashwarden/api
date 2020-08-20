<?php

namespace app\core\services;

use app\core\exceptions\InternalException;
use app\core\models\Account;
use app\core\models\Category;
use app\core\models\User;
use app\core\requests\JoinRequest;
use app\core\types\AccountType;
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
     * @throws \app\core\exceptions\InvalidArgumentException
     */
    public function createUserAfterInitData(User $user)
    {
        try {
            $account = new Account();
            $account->setAttributes([
                'name' => Yii::t('app', 'General Account'),
                'type' => AccountType::getName(AccountType::GENERAL_ACCOUNT),
                'user_id' => $user->id,
                'balance' => 0,
                'default' => Account::DEFAULT,
                'currency_code' => $user->base_currency_code
            ]);
            if (!$account->save()) {
                throw new DBException('Init Account fail ' . Setup::errorMessage($account->firstErrors));
            }
            $items = [
                [
                    'name' => Yii::t('app', 'Food and drink'),
                    'color' => ColorType::RED,
                    'icon_name' => 'food',
                    'direction' => DirectionType::OUT,
                    'default' => Category::NOT_DEFAULT
                ],
                [
                    'name' => Yii::t('app', 'Home life'),
                    'color' => ColorType::ORANGE,
                    'icon_name' => 'home',
                    'direction' => DirectionType::OUT,
                    'default' => Category::NOT_DEFAULT
                ],
                [
                    'name' => Yii::t('app', 'Traffic'),
                    'color' => ColorType::BLUE,
                    'icon_name' => 'bus',
                    'direction' => DirectionType::OUT,
                    'default' => Category::NOT_DEFAULT
                ],
                [
                    'name' => Yii::t('app', 'Recreation'),
                    'color' => ColorType::VOLCANO,
                    'icon_name' => 'game',
                    'direction' => DirectionType::OUT,
                    'default' => Category::NOT_DEFAULT
                ],
                [
                    'name' => Yii::t('app', 'Health care'),
                    'color' => ColorType::GREEN,
                    'icon_name' => 'medicine-chest',
                    'direction' => DirectionType::OUT,
                    'default' => Category::NOT_DEFAULT
                ],
                [
                    'name' => Yii::t('app', 'Clothes'),
                    'color' => ColorType::PURPLE,
                    'icon_name' => 'clothes',
                    'direction' => DirectionType::OUT,
                    'default' => Category::NOT_DEFAULT
                ],
                [
                    'name' => Yii::t('app', 'Cultural education'),
                    'color' => ColorType::CYAN,
                    'icon_name' => 'education',
                    'direction' => DirectionType::OUT,
                    'default' => Category::NOT_DEFAULT
                ],
                [
                    'name' => Yii::t('app', 'Investment expenditure'),
                    'color' => ColorType::GOLD,
                    'icon_name' => 'investment',
                    'direction' => DirectionType::OUT,
                    'default' => Category::NOT_DEFAULT
                ],
                [
                    'name' => Yii::t('app', 'Childcare'),
                    'color' => ColorType::LIME,
                    'icon_name' => 'baby',
                    'direction' => DirectionType::OUT,
                    'default' => Category::NOT_DEFAULT
                ],
                [
                    'name' => Yii::t('app', 'Other expenses'),
                    'color' => ColorType::GEEK_BLUE,
                    'icon_name' => 'expenses',
                    'direction' => DirectionType::OUT,
                    'default' => Account::DEFAULT,
                ],
                [
                    'name' => Yii::t('app', 'Work income'),
                    'color' => ColorType::BLUE,
                    'icon_name' => 'work',
                    'direction' => DirectionType::IN,
                    'default' => Category::NOT_DEFAULT
                ],
                [
                    'name' => Yii::t('app', 'Investment income'),
                    'color' => ColorType::GOLD,
                    'icon_name' => 'investment',
                    'direction' => DirectionType::IN,
                    'default' => Category::NOT_DEFAULT
                ],
                [
                    'name' => Yii::t('app', 'Other income'),
                    'color' => ColorType::MAGENTA,
                    'icon_name' => 'income',
                    'direction' => DirectionType::IN,
                    'default' => Category::DEFAULT,
                ],
            ];
            $time = date('Y-m-d H:i:s');
            $rows = [];
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
