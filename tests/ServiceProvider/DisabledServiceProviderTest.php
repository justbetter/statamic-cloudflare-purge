<?php

namespace JustBetter\StatamicCloudflarePurge\Tests\ServiceProvider;

use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Event;
use JustBetter\StatamicCloudflarePurge\Listeners\FlushCacheListener;
use JustBetter\StatamicCloudflarePurge\StatamicCloudflarePurgeServiceProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\StaticCacheCleared;
use Statamic\Testing\AddonTestCase;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class DisabledServiceProviderTest extends AddonTestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected string $addonServiceProvider = StatamicCloudflarePurgeServiceProvider::class;

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:7tG0yY7g3QkFrQ+Vk4EBSbcT8D9C4/5Dph1dNRjh6WU=');
        $app['config']->set('cache.default', 'array');
        $app['config']->set('cloudflare-purge.enabled', false);
        $app['config']->set('cloudflare-purge.zone', 'test-zone');
    }

    #[Test]
    public function it_does_not_register_flush_listeners_when_disabled(): void
    {
        $dispatcher = Event::getFacadeRoot();
        $this->assertInstanceOf(Dispatcher::class, $dispatcher);

        $listen = $dispatcher->getRawListeners();
        $flushListeners = $listen[StaticCacheCleared::class] ?? [];

        $this->assertIsArray($flushListeners);
        $this->assertFalse(in_array(FlushCacheListener::class, $flushListeners, true));
    }
}
