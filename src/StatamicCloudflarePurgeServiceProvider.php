<?php

namespace JustBetter\StatamicCloudflarePurge;

use Illuminate\Support\Facades\Event;
use JustBetter\StatamicCloudflarePurge\Commands\PurgeCommand;
use JustBetter\StatamicCloudflarePurge\Listeners\UrlInvalidatedListener;
use Statamic\Events\UrlInvalidated;
use Statamic\Providers\AddonServiceProvider;

class StatamicCloudflarePurgeServiceProvider extends AddonServiceProvider
{
    public function boot(): void
    {
        $this
            ->bootCommands()
            ->bootConfig()
            ->bootListeners()
            ->bootPublishables();
    }

    protected function bootCommands(): static
    {
        $this->commands([
            PurgeCommand::class,
        ]);

        return $this;
    }

    protected function bootConfig(): static
    {
        $this->mergeConfigFrom(__DIR__.'../config/cloudflare-purge.php', 'cloudflare-purge');

        return $this;
    }

    protected function bootListeners(): static
    {
        Event::listen(UrlInvalidated::class, UrlInvalidatedListener::class);

        return $this;
    }

    protected function bootPublishables(): static
    {
        $this->publishes([
            __DIR__.'../config/cloudflare-purge.php' => config_path('cloudflare-purge.php'),
        ]);

        return $this;
    }
}
