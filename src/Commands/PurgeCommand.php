<?php

namespace JustBetter\StatamicCloudflarePurge\Commands;

use Illuminate\Console\Command;
use JustBetter\StatamicCloudflarePurge\Jobs\PurgeCloudflareCachesJob;

class PurgeCommand extends Command
{
    public $signature = 'statamic:cloudflare:purge {--all : Purge everything, not just the invalidated files}';

    public $description = 'Purge the invalidated cloudflare cache';

    public function handle(): int
    {
        PurgeCloudflareCachesJob::dispatch(boolval($this->option('all')));

        return static::SUCCESS;
    }
}
