<?php

namespace JustBetter\StatamicCloudflarePurge\Listeners;

use JustBetter\StatamicCloudflarePurge\Facades\CloudflarePurge;
use Statamic\Events\UrlInvalidated;

class UrlInvalidatedListener
{
    public function handle(UrlInvalidated $event): void
    {
        if (! config('cloudflare-purge.enabled')) {
            return;
        }

        CloudflarePurge::appendInvalidateUrl($event->url);
    }
}
