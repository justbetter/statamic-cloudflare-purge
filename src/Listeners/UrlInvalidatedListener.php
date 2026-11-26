<?php

namespace JustBetter\StatamicCloudflarePurge\Listeners;

use JustBetter\StatamicCloudflarePurge\Facades\CloudflarePurge as CloudflarePurgeFacade;
use Statamic\Events\UrlInvalidated;

class UrlInvalidatedListener
{
    public function handle(UrlInvalidated $event): void
    {
        if (! CloudflarePurgeFacade::isEnabled()) {
            return;
        }

        CloudflarePurgeFacade::appendInvalidateUrl($event->url);
    }
}
