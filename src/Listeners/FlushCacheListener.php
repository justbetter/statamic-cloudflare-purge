<?php

namespace JustBetter\StatamicCloudflarePurge\Listeners;

use JustBetter\StatamicCloudflarePurge\Facades\CloudflarePurge as CloudflarePurgeFacade;
use JustBetter\StatamicCloudflarePurge\Jobs\PurgeCloudflareCachesJob;

class FlushCacheListener
{
    public function handle(mixed $event): void
    {
        if (! CloudflarePurgeFacade::isEnabled()) {
            return;
        }

        foreach (CloudflarePurgeFacade::getConfiguredZones() as $zone) {
            CloudflarePurgeFacade::appendZone($zone);
        }

        PurgeCloudflareCachesJob::dispatch(true);
    }
}
