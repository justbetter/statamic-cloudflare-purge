<?php

namespace JustBetter\StatamicCloudflarePurge\Listeners;

use JustBetter\StatamicCloudflarePurge\Facades\CloudflarePurge;
use JustBetter\StatamicCloudflarePurge\Jobs\PurgeCloudflareCachesJob;

class FlushCacheListener
{
    public function handle($event): void
    {
        CloudflarePurge::appendZone();
        
        PurgeCloudflareCachesJob::dispatch(true);
    }
}
