<?php

$hasQwenKey = filled(env('QWEN_API_KEY'));
$useOpenRouterFallback = ! $hasQwenKey && filled(env('OPENROUTER_API_KEY'));

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect' => env('GITHUB_REDIRECT_URI'),
    ],

    'qwen' => [
        'api_key' => $hasQwenKey ? env('QWEN_API_KEY') : env('OPENROUTER_API_KEY'),
        'base_url' => $useOpenRouterFallback
            ? env('OPENROUTER_URL', 'https://openrouter.ai/api/v1/chat/completions')
            : (env('QWEN_BASE_URL') ?: 'https://dashscope-intl.aliyuncs.com/compatible-mode/v1/chat/completions'),
        'model' => $useOpenRouterFallback
            ? env('OPENROUTER_MODEL', 'qwen/qwen-2.5-72b-instruct')
            : (env('QWEN_MODEL') ?: 'qwen-plus'),
        'timeout' => (int) (env('QWEN_TIMEOUT') ?: env('OPENROUTER_TIMEOUT', 30)),
        'retries' => (int) (env('QWEN_RETRIES') ?: env('OPENROUTER_RETRIES', 2)),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
