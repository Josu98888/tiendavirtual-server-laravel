<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie' , 'storage/public*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*, https://tiendavirtual-client-angular.vercel.app/'],

    'allowed_origins_patterns' => [],

    'allowedHeaders' => ['*'],

    'exposed_headers' => [],
    

    'Access-Control-Allow-Origin' => ['*'],

    'max_age' => 0,

    'supports_credentials' => false,

];
