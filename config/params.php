<?php

return [
    'appUrl' => env('APP_URL'),
    'adminEmail' => env('ADMIN_EMAIL'),
    'senderEmail' => env('SENDER_EMAIL'),
    'senderName' => env('SENDER_NAME', env('APP_NAME')),
    'telegramToken' => env('TELEGRAM_TOKEN'),
    'user.passwordResetTokenExpire' => env('USER_RESET_TOKEN_EXPIRE', 3600),
];
