<?php

namespace app\modules\v1\controllers;

use app\core\exceptions\InvalidArgumentException;
use sizeg\jwt\JwtHttpBearerAuth;
use Yii;
use yii\base\Model;
use yii\filters\Cors;
use yii\web\ForbiddenHttpException;
use yiier\helpers\SearchModel;
use yiier\helpers\Setup;

/**
 *
 * @property-read int $pageSize
 */
class ActiveController extends \yii\rest\ActiveController
{
    protected const MAX_PAGE_SIZE = 100;
    protected const DEFAULT_PAGE_SIZE = 20;
    public $defaultOrder = ['id' => SORT_DESC];
    public $partialMatchAttributes = [];

    /**
     * 不参与校验的 actions
     * @var array
     */
    public $noAuthActions = [];

    // 序列化输出
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // 跨区请求 必须先删掉 authenticator
        $behaviors['authenticator'];
        unset($behaviors['authenticator']);

        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Max-Age' => 86400,
            ]
        ];
        $behaviors['authenticator'] = [
            'class' => JwtHttpBearerAuth::class,
            'optional' => array_merge($this->noAuthActions, ['options']),
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        return $actions;
    }

    /**
     * @return \yii\data\ActiveDataProvider
     */
    public function prepareDataProvider()
    {
        $modelClass = $this->modelClass;
        $searchModel = new SearchModel([
            'defaultOrder' => $this->defaultOrder,
            'model' => $modelClass,
            'scenario' => 'default',
            'partialMatchAttributes' => $this->partialMatchAttributes,
            'pageSize' => $this->getPageSize()
        ]);

        $dataProvider = $searchModel->search(['SearchModel' => Yii::$app->request->queryParams]);
        $dataProvider->query->andWhere(['user_id' => Yii::$app->user->id]);
        return $dataProvider;
    }

    /**
     * @return int
     */
    protected function getPageSize()
    {
        if ($pageSize = (int)request('pageSize')) {
            if ($pageSize < self::MAX_PAGE_SIZE) {
                return $pageSize;
            }
            return self::MAX_PAGE_SIZE;
        }
        return self::DEFAULT_PAGE_SIZE;
    }


    /**
     * @param Model $model
     * @param array $params
     * @return Model
     * @throws InvalidArgumentException
     */
    public function validate(Model $model, array $params): Model
    {
        $model->load($params, '');
        if (!$model->validate()) {
            throw new InvalidArgumentException(Setup::errorMessage($model->firstErrors));
        }
        return $model;
    }

    /**
     * @param string $action
     * @param null $model
     * @param array $params
     * @throws ForbiddenHttpException
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        if (in_array($action, ['delete', 'update', 'view'])) {
            if ($model->user_id !== \Yii::$app->user->id) {
                throw new ForbiddenHttpException(
                    t('app', 'You can only ' . $action . ' data that you\'ve created.')
                );
            }
        }
    }
}
