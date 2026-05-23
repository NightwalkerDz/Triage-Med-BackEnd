<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
    'https://triagemed.vercel.app',
    'https://triage-med-front-end-cid9.vercel.app',
],

    'allowed_origins_patterns' => [
        '#^https?://(localhost|127\.0\.0\.1|\[::1\])(:\d+)?$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
