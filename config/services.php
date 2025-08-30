<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Reverb Apps
    |--------------------------------------------------------------------------
    |
    | You may define the apps that are supported by your Reverb server. You
    | should define your "app id", "app key", and "app secret" values.
    |
    */

    'apps' => [
        [
            'id' => env('REVERB_APP_ID'),
            'name' => env('APP_NAME', 'Laravel'),
            'key' => env('VITE_REVERB_APP_KEY'),
            'secret' => env('REVERB_APP_SECRET'),
            'capacity' => null,
            'allowed_origins' => [
                env('APP_URL'),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Server Host / Port
    |--------------------------------------------------------------------------
    |
    | The Reverb server runs on the given host and port. The default should
    | work in most cases, but you are free to change them if needed.
    |
    */

    'host' => env('VITE_REVERB_HOST', '127.0.0.1'),
    'port' => env('VITE_REVERB_PORT', 8080),

    /*
    |--------------------------------------------------------------------------
    | Scheme
    |--------------------------------------------------------------------------
    |
    | This value determines the scheme (http or https) that should be used
    | when broadcasting events from the server to the clients.
    |
    */

    'scheme' => env('VITE_REVERB_SCHEME', 'http'),

    /*
    |--------------------------------------------------------------------------
    | Ping Interval
    |--------------------------------------------------------------------------
    |
    | This value determines how often the server should send a ping frame to
    | clients to keep the WebSocket connection alive (in seconds).
    |
    */

    'ping_interval' => 30,

    /*
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    |
    | If enabled, Reverb will collect statistics about connections, peak
    | connections, messages sent, and messages received.
    |
    */

    'collect_statistics' => true,

    'validemail' => [
        'key' => env('VALIDEMAIL_KEY'),
        'endpoint' => env('VALIDEMAIL_ENDPOINT', 'https://api.ValidEmail.net/'),
        'min_score' => (int) env('VALIDEMAIL_MIN_SCORE', 80),
        'enabled' => filter_var(env('USE_VALIDEMAIL_API', true), FILTER_VALIDATE_BOOLEAN),
    ],
];
