<?php

return [
    // datos de contacto que se muestran en la pagina
    'correo' => env('CONTACTO_CORREO', 'contacto@tampu.pe'),
    'whatsapp' => env('CONTACTO_WHATSAPP'),

    // los correos que terminan en este dominio son de estudiantes de la universidad
    'dominio_institucional' => env('INSTITUCION_DOMINIO', 'continental.edu.pe'),

    // pedir el codigo al correo solo si de verdad se pueden mandar correos (o en local, donde se muestra en pantalla)
    'verificar_correo' => env('VERIFICAR_CORREO', env('MAIL_MAILER', 'log') !== 'log' || env('APP_ENV') === 'local'),

    // entrar con Google o Microsoft. Si no hay client id, el boton no aparece
    'oauth' => [
        'google' => [
            'nombre' => 'Google',
            'id' => env('GOOGLE_CLIENT_ID'),
            'secreto' => env('GOOGLE_CLIENT_SECRET'),
            'autorizar' => env('GOOGLE_AUTH_URL', 'https://accounts.google.com/o/oauth2/v2/auth'),
            'token' => env('GOOGLE_TOKEN_URL', 'https://oauth2.googleapis.com/token'),
            'usuario' => env('GOOGLE_USERINFO_URL', 'https://openidconnect.googleapis.com/v1/userinfo'),
        ],
        // el tenant es el de la universidad, asi solo entran sus cuentas y nadie puede
        // crear otra organizacion en Microsoft con un correo falso de la universidad
        'microsoft' => [
            'nombre' => 'Microsoft',
            'id' => env('MICROSOFT_CLIENT_ID'),
            'secreto' => env('MICROSOFT_CLIENT_SECRET'),
            'autorizar' => env('MICROSOFT_AUTH_URL', 'https://login.microsoftonline.com/' . env('MICROSOFT_TENANT', env('INSTITUCION_DOMINIO', 'continental.edu.pe')) . '/oauth2/v2.0/authorize'),
            'token' => env('MICROSOFT_TOKEN_URL', 'https://login.microsoftonline.com/' . env('MICROSOFT_TENANT', env('INSTITUCION_DOMINIO', 'continental.edu.pe')) . '/oauth2/v2.0/token'),
            'usuario' => env('MICROSOFT_USERINFO_URL', 'https://graph.microsoft.com/oidc/userinfo'),
        ],
    ],
];
