<?php

return [
    'appUrl' => env('APP_URL'),
    'adminEmail' => env('ADMIN_EMAIL'),
    'senderEmail' => env('SENDER_EMAIL'),
    'senderName' => env('SENDER_NAME', env('APP_NAME')),
    'telegramToken' => env('TELEGRAM_TOKEN'),
    'telegramBotName' => env('TELEGRAM_BOT_NAME'),
    'user.passwordResetTokenExpire' => env('USER_RESET_TOKEN_EXPIRE', 3600),
    'seoKeywords' => env('SEO_KEYWORDS'),
    'seoDescription' => env('SEO_DESCRIPTION'),
    'googleAnalyticsAU' => env('GOOGLE_ANALYTICS_AU'),
    // 不记录 header 指定 key 的值到日志，默认值为 authorization，配置自定义会覆盖默认值
    'logFilterIgnoredHeaderKeys' => env('LOG_FILTER_IGNORED_HEADER_KEYS', 'authorization,token,cookie'),
    'logFilterIgnoredKeys' => env('LOG_FILTER_IGNORED_KEYS', 'password'), // 不记录日志
    'logFilterHideKeys' => env('LOG_FILTER_HIDE_KEYS'), // 用*代替所有数据
    'logFilterHalfHideKeys' => env('LOG_FILTER_HALF_HIDE_KEYS', 'email'), // 部分数据隐藏，只显示头部 20% 和尾部 20% 数据，剩下的用*代替
    'uploadSavePath' => '@webroot/uploads',
    'uploadWebPath' => '@web/uploads',
];
