<?php

return [
    'domain' => env('AUTH0_DOMAIN', ''),
    'clientId' => env('AUTH0_CLIENT_ID', '_'),
    'cookieSecret' => env('AUTH0_COOKIE_SECRET', '_'),
    'adminScopes' => explode(',', env('AUTH0_ADMIN_SCOPES', '')),
    'audience' => explode(',', env('AUTH0_AUDIENCE', '')),
];