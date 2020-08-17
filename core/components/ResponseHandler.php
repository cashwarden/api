<?php

namespace app\core\components;

use yii\web\Response;

class ResponseHandler
{
    public $event;
    public $successMessage;

    /**
     * 返回数据统一处理
     */
    public function formatResponse()
    {
        $response = $this->event->sender;
        if ($response->data !== null) {
            if (isset($response->data['code']) && isset($response->data['message'])) {
                $response->data = [
                    'code' => $response->data['code'] ?: $response->statusCode,
                    'data' => isset($response->data['data']) ? $response->data['data'] : null,
                    'message' => $response->data['message'],
                ];
            } elseif ($response->format != 'html' && !isset($response->data['message'])) {
                $response->data = [
                    'code' => 0,
                    'data' => $response->data,
                    'message' => $this->successMessage ?: \Yii::t('app', 'Success Message'),
                ];
            } elseif ((!empty($response->data['message'])) && !isset($response->data['code'])) {
                $message = $response->data['message'];
                unset($response->data['message']);
                $response->data = [
                    'code' => 0,
                    'data' => isset($response->data[0]) ? $response->data[0] : $response->data,
                    'message' => $message,
                ];
            }
        }
        $this->formatHttpStatusCode($response);
    }

    public function formatHttpStatusCode(Response $response)
    {
        switch ($response->statusCode) {
            case 404:
                $response->data['code'] = 404;
                $response->data['message'] = null;
                break;
            case 204:
                if (\Yii::$app->request->isDelete) {
                    $response->data['code'] = 0;
                    $response->data['data'] = null;
                    $response->data['message'] = $this->successMessage ?: \Yii::t('app', 'Success Message');
                }
                break;
            case 422:
                $response->data['code'] = 422;
                $response->data['message'] = current($response->data['data'])['message'];
                break;
            default:
                # code...
                break;
        }

        $response->setStatusCode(200);
    }
}
