<?php

namespace app\core\helpers;

use app\core\exceptions\ThirdPartyServiceErrorException;
use app\core\traits\SendRequestTrait;
use GuzzleHttp\Exception\GuzzleException;
use yiier\graylog\Log;

class HolidayHelper
{
    use SendRequestTrait;

    /**
     * @return mixed
     * @throws ThirdPartyServiceErrorException
     */
    public static function getNextWorkday()
    {
        $baseUrl = 'http://timor.tech/api/holiday/workday/next';
        /** @var HolidayHelper $self */

        try {
            $self = \Yii::createObject(self::class);
            $response = $self->sendRequest('GET', $baseUrl);
            $data = json_decode($response);
            if ($data->code == 0) {
                return $data->workday->date;
            }
        } catch (GuzzleException $e) {
            Log::error('holiday error', [$response ?? [], (string)$e]);
            throw new ThirdPartyServiceErrorException();
        } catch (\Exception $e) {
            Log::error('holiday error', [$response ?? [], (string)$e]);
            throw new ThirdPartyServiceErrorException();
        }
    }
}
