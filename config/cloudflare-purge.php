<?php

return [
    'enabled' => env('CLOUDFLARE_PURGING_ENABLED', false),
    'endpoint' => env('CLOUDFLARE_API_ENDPOINT', 'https://api.cloudflare.com/client/v4'),
    'token' => env('CLOUDFLARE_API_TOKEN'),
    'zone' => env('CLOUDFLARE_ZONE'),

    // The events that, when dispatched, will cause the full cache to be purged
    'flush-events' => [
        \Statamic\Events\GlobalSetSaved::class,
        \Statamic\Events\NavSaved::class,
        \Statamic\Events\StaticCacheCleared::class,
    ],
];
