<?php

namespace JustBetter\StatamicCloudflarePurge\Listeners;

use JustBetter\StatamicCloudflarePurge\Jobs\PurgeCloudflareCaches;

class FlushCacheListener
{
    public function handle($event): void
    {
        PurgeCloudflareCaches::dispatch(true);
    }
}