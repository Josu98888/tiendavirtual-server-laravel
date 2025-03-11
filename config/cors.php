<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie' , 'storage/public*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowedHeaders' => ['*'],

    'exposed_headers' => [],
    
    'max_age' => 0,

    'supports_credentials' => false,

];
