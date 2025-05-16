<?php

return [
    'endpoint' => env('CLOUDFLARE_API_ENDPOINT', 'https://api.cloudflare.com/client/v4'),
    'token' => env('CLOUDFLARE_API_TOKEN'),
    'zone' => env('CLOUDFLARE_ZONE'),
];
