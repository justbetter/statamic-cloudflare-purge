<?php

namespace JustBetter\StatamicCloudflarePurge\Commands;

use Illuminate\Console\Command;
use JustBetter\StatamicCloudflarePurge\Facades\CloudflarePurge as CloudflarePurgeFacade;
use JustBetter\StatamicCloudflarePurge\Jobs\PurgeCloudflareCachesJob;

class PurgeCommand extends Command
{
    public $signature = 'statamic:cloudflare:purge {--all : Purge everything, not just the invalidated files}';

    public $description = 'Purge the invalidated cloudflare cache';

    public function handle(): int
    {
        if (! CloudflarePurgeFacade::isEnabled()) {
            $this->info('Purging has been disabled.');

            return static::FAILURE;
        }

        PurgeCloudflareCachesJob::dispatch(boolval($this->option('all')));

        return static::SUCCESS;
    }
}
