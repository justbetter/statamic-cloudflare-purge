<?php

namespace JustBetter\StatamicCloudflarePurge\Tests\ServiceProvider;

use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use JustBetter\StatamicCloudflarePurge\Commands\PurgeCommand;
use JustBetter\StatamicCloudflarePurge\Listeners\FlushCacheListener;
use JustBetter\StatamicCloudflarePurge\Listeners\UrlInvalidatedListener;
use JustBetter\StatamicCloudflarePurge\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\StaticCacheCleared;
use Statamic\Events\UrlInvalidated;
use Statamic\Facades\Addon;

class ServiceProviderTest extends TestCase
{
    #[Test]
    public function it_registers_the_addon(): void
    {
        $this->assertSame(
            'justbetter/statamic-cloudflare-purge',
            Addon::get('justbetter/statamic-cloudflare-purge')->id()
        );
    }

    #[Test]
    public function it_registers_the_purge_command(): void
    {
        $this->assertArrayHasKey('statamic:cloudflare:purge', Artisan::all());
        $this->assertInstanceOf(
            PurgeCommand::class,
            Artisan::all()['statamic:cloudflare:purge']
        );
    }

    #[Test]
    public function it_registers_listeners_when_enabled(): void
    {
        $dispatcher = Event::getFacadeRoot();
        $this->assertInstanceOf(Dispatcher::class, $dispatcher);

        $listen = $dispatcher->getRawListeners();

        $urlListeners = $listen[UrlInvalidated::class] ?? [];
        $flushListeners = $listen[StaticCacheCleared::class] ?? [];

        $this->assertIsArray($urlListeners);
        $this->assertIsArray($flushListeners);
        $this->assertContains(UrlInvalidatedListener::class, $urlListeners);
        $this->assertContains(FlushCacheListener::class, $flushListeners);
    }
}
