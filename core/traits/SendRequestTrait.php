<?php

namespace app\core\traits;

use GuzzleHttp\Exception\GuzzleException;
use yii\base\ErrorException;
use yii\helpers\ArrayHelper;
use yiier\graylog\Log;

trait SendRequestTrait
{
    /**
     * @param string $type
     * @param string $apiUrl
     * @param array $options
     * @return string
     * @throws ErrorException|GuzzleException
     * @throws \Exception
     */
    protected function sendRequest(string $type, string $apiUrl, array $options = []): string
    {
        $data = ArrayHelper::getValue($options, 'query', []);
        $beginMillisecond = round(microtime(true) * 1000);
        try {
            $baseOptions = [
                'query' => $data,
                'timeout' => 10000, // Response timeout
                'connect_timeout' => 10000, // Connection timeout
            ];
            $client = new \GuzzleHttp\Client(['verify' => false]);
            $response = $client->request($type, $apiUrl, array_merge($baseOptions, $options));
            Log::info('Curl 请求服务开始', ['url' => $apiUrl, $data, (array)$response]);
        } catch (\Exception $e) {
            Log::error('Curl 请求服务异常', ['url' => $apiUrl, 'exception' => (string)$e, $data]);
            throw new ErrorException('Curl 请求异常:' . $e->getMessage(), 500001);
        }
        if ($response->getStatusCode() == 200) {
            // 记录 curl 耗时
            $endMillisecond = round(microtime(true) * 1000);
            $context = [
                'curlUrl' => $apiUrl,
                'CurlSpendingMillisecond' => $endMillisecond - $beginMillisecond
            ];
            Log::info('curl time consuming', [$context, $data,]);
            return (string)$response->getBody();
        } else {
            Log::error('Curl 请求服务成功，但是操作失败', ['url' => $apiUrl, 'data' => $data]);
            throw new ErrorException('Curl 请求服务成功，但是操作失败：');
        }
    }
}
