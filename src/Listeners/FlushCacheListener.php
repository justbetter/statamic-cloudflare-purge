<?php

namespace JustBetter\StatamicCloudflarePurge\Listeners;

use JustBetter\StatamicCloudflarePurge\Jobs\PurgeCloudflareCachesJob;

class FlushCacheListener
{
    public function handle($event): void
    {
        PurgeCloudflareCachesJob::dispatch(true);
    }
}
