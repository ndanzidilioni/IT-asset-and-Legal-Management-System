<?php

return [

    // Endpoints that need CORS and cookies
    'paths' => [
        'sanctum/csrf-cookie',
        'login-web',
        'logout-web',
        'api/*',
    ],

    // Allow all HTTP methods
    'allowed_methods' => ['*'],

    // Allow your frontend origin(s)
    // If using React dev server:
    //   http://localhost:3000 or http://127.0.0.1:3000
    // If serving elsewhere, put that origin here.
    'allowed_origins' => [
        'http://localhost',
        'http://127.0.0.1',
        'http://localhost:3000',
        'http://127.0.0.1:3000',
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'http://10.10.100.193:3000',
    ],

    // Or use patterns if you prefer
    'allowed_origins_patterns' => [],

    // Allow all headers
    'allowed_headers' => ['*'],

    // Optional: expose specific headers to the browser
    'exposed_headers' => [],

    // Cache preflight (OPTIONS) response
    'max_age' => 86400,

    // Important for Sanctum cookie auth
    'supports_credentials' => true,
];