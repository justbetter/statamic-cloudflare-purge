<?php

namespace JustBetter\StatamicCloudflarePurge\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use JustBetter\StatamicCloudflarePurge\Facades\CloudflarePurge;
use JustBetter\StatamicCloudflarePurge\Integrations\Cloudflare;

class PurgeCloudflareCachesJob implements ShouldQueue
{
    use Queueable;

    public function __construct(protected bool $all = false) {}

    public function handle(Cloudflare $cloudflare): void
    {
        if (! config('cloudflare-purge.enabled')) {
            return;
        }

        foreach (CloudflarePurge::getZones() as $zone) {
            $files = CloudflarePurge::popInvalidateUrls($zone);

            $cloudflare->purge($zone, everything: $this->all, files: $files);
        }
    }
}
