<?php

namespace app\core\exceptions;

use Yii;
use yii\web\HttpException;

class ThirdPartyServiceErrorException extends HttpException
{
    /**
     * Constructor.
     * @param string $message error message
     * @param int $code error code
     * @param \Exception|null $previous The previous exception used for the exception chaining.
     */
    public function __construct(
        $message = '',
        $code = ErrorCodes::THIRD_PARTY_SERVICE_ERROR,
        \Exception $previous = null
    ) {
        $message = $message ?: Yii::t('app/error', ErrorCodes::THIRD_PARTY_SERVICE_ERROR);
        parent::__construct(200, $message, $code, $previous);
    }
}
