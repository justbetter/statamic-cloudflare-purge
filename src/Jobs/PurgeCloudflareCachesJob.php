<?php

namespace JustBetter\StatamicCloudflarePurge\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use JustBetter\StatamicCloudflarePurge\Facades\CloudflarePurge;
use JustBetter\StatamicCloudflarePurge\Integrations\Cloudflare;

class PurgeCloudflareCachesJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public bool $all = false) {}

    public function handle(Cloudflare $cloudflare): void
    {
        $files = CloudflarePurge::popInvalidateUrls();

        $cloudflare->purge(everything: $this->all, files: $files);
    }
}
