<?php

namespace JustBetter\StatamicCloudflarePurge;

use Illuminate\Support\Facades\Event;
use JustBetter\StatamicCloudflarePurge\Listeners\FlushCacheListener;
use Statamic\Providers\AddonServiceProvider;

class StatamicCloudflarePurgeServiceProvider extends AddonServiceProvider
{
    public function bootAddon(): void
    {
        $this
            ->bootConfig()
            ->bootListeners();
    }

    protected function bootConfig(): static
    {
        $this->mergeConfigFrom(__DIR__.'/../config/cloudflare-purge.php', 'cloudflare-purge');
        $this->publishes([
            __DIR__.'/../config/cloudflare-purge.php' => config_path('cloudflare-purge.php'),
        ]);

        return $this;
    }

    protected function bootListeners(): static
    {
        if (! config('cloudflare-purge.enabled')) {
            return $this;
        }

        $events = config('cloudflare-purge.flush-events');

        if (is_string($events) || is_array($events)) {
            Event::listen($events, FlushCacheListener::class);
        }

        return $this;
    }
}
