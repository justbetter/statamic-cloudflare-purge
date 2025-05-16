<?php

namespace JustBetter\StatamicCloudflarePurge\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use JustBetter\StatamicCloudflarePurge\Facades\CloudflarePurge;
use JustBetter\StatamicCloudflarePurge\Integrations\Cloudflare;

class PurgeCloudflareCaches implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public function __construct(protected Cloudflare $cloudflare) {}

    public function handle(bool $all = false): void
    {
        $files = CloudflarePurge::popInvalidateUrls();

        $this->cloudflare->purge(everything: $all, files: $files);
    }
}
