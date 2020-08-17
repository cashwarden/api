<?php

declare(strict_types=1);

namespace app\controllers;

use Yii;
use yii\base\UserException;
use yii\rest\Controller;

class SiteController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        return 'hello yii';
    }

    /**
     * @return string
     */
    public function actionHealthCheck()
    {
        return 'OK';
    }

    /**
     * @return array
     */
    public function actionError(): array
    {
        $exception = Yii::$app->errorHandler->exception;
        if ($exception !== null) {
            $array = [];
            Yii::error([
                'request_id' => Yii::$app->requestId->id,
                'exception' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ], 'response_data_error');

            if (YII_DEBUG) {
                $array['type'] = get_class($exception);
                if (!$exception instanceof UserException) {
                    $array['file'] = $exception->getFile();
                    $array['line'] = $exception->getLine();
                    $array['stack-trace'] = explode("\n", $exception->getTraceAsString());
                    if ($exception instanceof \yii\db\Exception) {
                        $array['error-info'] = $exception->errorInfo;
                    }
                }
            }
            if (($prev = $exception->getPrevious()) !== null) {
                $array['previous'] = $this->convertExceptionToArray($prev);
            }
            return ['code' => $exception->getCode(), 'message' => $exception->getMessage()] + $array;
        }
        return [];
    }
}
