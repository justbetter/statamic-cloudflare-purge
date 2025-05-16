<?php

namespace JustBetter\StatamicCloudflarePurge\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use JustBetter\StatamicCloudflarePurge\Integrations\Cloudflare;

class PurgeCloudflareCaches implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public function __construct(protected Cloudflare $cloudflare) {}

    public function handle(bool $all = false)
    {
        $files = explode("\n", Storage::disk('local')->get('/.cloudflare-invalidate-urls'));
        $files = Arr::where($files, fn($file) => $file);

        Storage::disk('local')->delete('/.cloudflare-invalidate-urls');

        $this->cloudflare->purge(everything: $all, files: $files);
    }
}
