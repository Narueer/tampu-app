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

    // asistente virtual: respaldo con IA cuando las reglas no entienden (ver app/Services/AsistenteIA.php)
    'ia' => [
        'proveedor' => env('IA_PROVEEDOR', 'gemini'),
        'clave' => env('IA_CLAVE'),
        'modelo' => env('IA_MODELO'),
        'url' => env('IA_URL'),
        'respaldo_proveedor' => env('IA_RESPALDO_PROVEEDOR', 'groq'),
        'respaldo_clave' => env('IA_RESPALDO_CLAVE'),
        'respaldo_modelo' => env('IA_RESPALDO_MODELO'),
        'tiempo' => env('IA_TIEMPO', 2.5),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
