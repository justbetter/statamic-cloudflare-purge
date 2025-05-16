<?php

namespace JustBetter\StatamicCloudflarePurge\Listeners;

use JustBetter\StatamicCloudflarePurge\Facades\CloudflarePurge;
use Statamic\Events\UrlInvalidated;

class UrlInvalidatedListener
{
    public function handle(UrlInvalidated $event): void
    {
        CloudflarePurge::appendInvalidateUrl($event->url);
    }
}
