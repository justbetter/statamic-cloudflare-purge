<?php

namespace JustBetter\StatamicCloudflarePurge\Listeners;

use JustBetter\StatamicCloudflarePurge\Facades\CloudflarePurge;
use JustBetter\StatamicCloudflarePurge\Jobs\PurgeCloudflareCachesJob;

class FlushCacheListener
{
    public function handle(mixed $event): void
    {
        if (! config('cloudflare-purge.enabled')) {
            return;
        }

        CloudflarePurge::appendZone();

        PurgeCloudflareCachesJob::dispatch(true);
    }
}
