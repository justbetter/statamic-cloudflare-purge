<?php

namespace JustBetter\StatamicCloudflarePurge\Listeners;

use Illuminate\Support\Facades\Storage;
use Statamic\Events\UrlInvalidated;

class UrlInvalidatedListener
{
    public function handle(UrlInvalidated $event): void
    {
        Storage::disk('local')->append('/.cloudflare-invalidate-urls', $event->url);
    }
}
