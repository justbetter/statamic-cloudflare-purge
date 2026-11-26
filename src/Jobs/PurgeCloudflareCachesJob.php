<?php

namespace JustBetter\StatamicCloudflarePurge\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use JustBetter\StatamicCloudflarePurge\Facades\CloudflarePurge as CloudflarePurgeFacade;
use JustBetter\StatamicCloudflarePurge\Integrations\Cloudflare;

class PurgeCloudflareCachesJob implements ShouldQueue
{
    use Queueable;

    public function __construct(protected bool $all = false) {}

    public function handle(Cloudflare $cloudflare): void
    {
        if (! CloudflarePurgeFacade::isEnabled()) {
            return;
        }

        foreach (CloudflarePurgeFacade::getZones() as $zone) {
            $files = CloudflarePurgeFacade::popInvalidateUrls($zone);

            $cloudflare->purge($zone, everything: $this->all, files: $files);
        }
    }
}
