<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, SparkPost and others. This file provides a sane default
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    // Agente de WhatsApp: token que manda en el encabezado X-Agente-Token.
    'agente' => [
        'token' => env('AGENTE_TOKEN'),
    ],

    // Refresco automático de la bandeja de comandas (docs/plan-bandeja-tiempo-real.md).
    // intervalo: segundos entre consultas mientras no haya websockets.
    // websockets: true solo cuando laravel-websockets esté instalado y corriendo; la pantalla
    // baja al intervalo_respaldo únicamente si el socket se conecta, si no sigue con intervalo.
    'bandeja' => [
        'intervalo' => (int) env('BANDEJA_INTERVALO', 5),
        'websockets' => (bool) env('BANDEJA_WEBSOCKETS', false),
        'intervalo_respaldo' => (int) env('BANDEJA_INTERVALO_RESPALDO', 60),
    ],

];
