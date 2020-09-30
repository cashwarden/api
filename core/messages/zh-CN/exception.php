<?php

use app\core\exceptions\ErrorCodes;

return [
    ErrorCodes::INVALID_ARGUMENT_ERROR => '参数异常',
    ErrorCodes::CANNOT_OPERATE_ERROR => '不能操作',
    ErrorCodes::THIRD_PARTY_SERVICE_ERROR => '第三方服务异常',
    ErrorCodes::FILE_ENCODING_ERROR => '文件编码必须为 UTF-8 格式',
];
