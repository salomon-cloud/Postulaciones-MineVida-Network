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
        'token' => env('POSTMARK_TOKEN'),
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

    'discord' => [
        'client_id' => env('DISCORD_CLIENT_ID'),
        'client_secret' => env('DISCORD_CLIENT_SECRET'),
        'redirect' => env('DISCORD_REDIRECT_URI'),
        'guild_id' => env('DISCORD_GUILD_ID'),
        'bot_token' => env('DISCORD_BOT_TOKEN'),
    ],

    'lumoryx_bot' => [
        'api_url' => env('DISCORD_BOT_API_URL', 'http://127.0.0.1:3001'),
        'internal_token' => env('INTERNAL_BOT_API_TOKEN'),
        'staff_channel_id' => env('DISCORD_STAFF_CHANNEL_ID'),
        'embed_icon_url' => env('DISCORD_EMBED_ICON_URL'),
    ],

];
