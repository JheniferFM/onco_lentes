<?php

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

    'replicate' => [
        'token' => env('REPLICATE_API_TOKEN'),
        'enhance_version' => env('REPLICATE_REAL_ESRGAN_VERSION', '42fed1c4974f88d4f1d6f0de12f4f6b5f0a8f6fadc5f59ab2338f5b6c15d10f2'),
        'classifier_model' => env('REPLICATE_CLASSIFIER_MODEL', 'lucataco/skin-lesion-classification:8cfb62e4'),
    ],

];
