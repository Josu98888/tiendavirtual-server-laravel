<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie' , 'storage/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'Access-Control-Allow-Origin' => ['*'],

    'max_age' => 0,

    'supports_credentials' => false,

];
