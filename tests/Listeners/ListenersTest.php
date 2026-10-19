<?php

namespace JustBetter\StatamicCloudflarePurge\Tests\Listeners;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use JustBetter\StatamicCloudflarePurge\CloudflarePurge;
use JustBetter\StatamicCloudflarePurge\Jobs\PurgeCloudflareCachesJob;
use JustBetter\StatamicCloudflarePurge\Listeners\FlushCacheListener;
use JustBetter\StatamicCloudflarePurge\Listeners\UrlInvalidatedListener;
use JustBetter\StatamicCloudflarePurge\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use ReflectionProperty;
use Statamic\Events\StaticCacheCleared;
use Statamic\Events\UrlInvalidated;
use Statamic\Facades\Site as SiteFacade;

class ListenersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    #[Test]
    public function url_invalidated_listener_appends_url(): void
    {
        $event = new UrlInvalidated('/page');

        (new UrlInvalidatedListener)->handle($event);

        $this->assertSame(
            [$event->url],
            array_values(CloudflarePurge::getInvalidateUrls())
        );
    }

    #[Test]
    public function url_invalidated_listener_skips_when_disabled(): void
    {
        config()->set('cloudflare-purge.enabled', false);

        (new UrlInvalidatedListener)->handle(new UrlInvalidated('/page'));

        $this->assertSame([], array_values(CloudflarePurge::getInvalidateUrls()));
    }

    #[Test]
    public function flush_cache_listener_appends_zone_and_dispatches_job(): void
    {
        Bus::fake();

        (new FlushCacheListener)->handle(new StaticCacheCleared);

        $this->assertSame(['test-zone'], array_values(CloudflarePurge::getZones()));

        Bus::assertDispatched(PurgeCloudflareCachesJob::class, function (PurgeCloudflareCachesJob $job): bool {
            return (new ReflectionProperty(PurgeCloudflareCachesJob::class, 'all'))->getValue($job) === true;
        });
    }

    #[Test]
    public function flush_cache_listener_skips_when_disabled(): void
    {
        config()->set('cloudflare-purge.enabled', false);
        Bus::fake();

        (new FlushCacheListener)->handle(new StaticCacheCleared);

        $this->assertSame([], array_values(CloudflarePurge::getZones()));
        Bus::assertNothingDispatched();
    }

    #[Test]
    public function url_invalidated_listener_appends_url_to_the_zone_of_its_site(): void
    {
        $this->setUpMultisite();

        (new UrlInvalidatedListener)->handle(new UrlInvalidated('/page', 'https://example.de'));

        $this->assertSame(['https://example.de/page'], array_values(CloudflarePurge::getInvalidateUrls('zone-de')));
        $this->assertSame([], array_values(CloudflarePurge::getInvalidateUrls('zone-nl')));
        $this->assertSame(['zone-de'], array_values(CloudflarePurge::getZones()));
    }

    #[Test]
    public function flush_cache_listener_appends_all_configured_zones(): void
    {
        Bus::fake();
        $this->setUpMultisite();

        (new FlushCacheListener)->handle(new StaticCacheCleared);

        $this->assertSame(['zone-nl', 'zone-de'], array_values(CloudflarePurge::getZones()));
        Bus::assertDispatched(PurgeCloudflareCachesJob::class);
    }

    protected function setUpMultisite(): void
    {
        SiteFacade::setSites([
            'nl' => ['name' => 'NL', 'url' => 'https://example.nl/', 'locale' => 'nl_NL'],
            'de' => ['name' => 'DE', 'url' => 'https://example.de/', 'locale' => 'de_DE'],
        ]);
        config()->set('cloudflare-purge.zone', [
            'nl' => 'zone-nl',
            'de' => 'zone-de',
        ]);
    }
}
