<?php

namespace JustBetter\StatamicCloudflarePurge\Commands;

use Illuminate\Console\Command;
use JustBetter\StatamicCloudflarePurge\Jobs\PurgeCloudflareCaches;

class PurgeCommand extends Command
{
    public $signature = 'statamic:cloudflare:purge {--all : Purge everything, not just the invalidated files}';

    public $description = 'Purge the invalidated cloudflare cache';

    public function __construct(protected PurgeCloudflareCaches $purgeCloudflareCachesJob)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        if ($this->option('all')) {
            $this->purgeCloudflareCachesJob->all = true;
        }

        $this->purgeCloudflareCachesJob->handle();

        return static::SUCCESS;
    }
}
