<?php

namespace JustBetter\StatamicCloudflarePurge\Tests\CloudflarePurge;

use Illuminate\Support\Facades\Storage;
use JustBetter\StatamicCloudflarePurge\CloudflarePurge;
use JustBetter\StatamicCloudflarePurge\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Site as SiteFacade;
use Statamic\Sites\Site;

class CloudflarePurgeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    #[Test]
    public function it_appends_and_reads_invalidate_urls(): void
    {
        CloudflarePurge::appendInvalidateUrl('https://example.com/a');
        CloudflarePurge::appendInvalidateUrl('https://example.com/b');
        CloudflarePurge::appendInvalidateUrl('https://example.com/a');

        $this->assertSame(
            ['https://example.com/a', 'https://example.com/b'],
            array_values(CloudflarePurge::getInvalidateUrls())
        );
        $this->assertSame(['test-zone'], array_values(CloudflarePurge::getZones()));
    }

    #[Test]
    public function it_pops_and_clears_invalidate_urls(): void
    {
        CloudflarePurge::appendInvalidateUrl('https://example.com/a', 'zone-a');

        $this->assertSame(
            ['https://example.com/a'],
            array_values(CloudflarePurge::popInvalidateUrls('zone-a'))
        );
        $this->assertSame([], array_values(CloudflarePurge::getInvalidateUrls('zone-a')));
        $this->assertSame([], array_values(CloudflarePurge::getZones()));
    }

    #[Test]
    public function it_skips_removing_unknown_zones(): void
    {
        CloudflarePurge::removeZone('missing-zone');

        $this->assertSame([], array_values(CloudflarePurge::getZones()));
    }

    #[Test]
    public function it_skips_appending_existing_zones(): void
    {
        CloudflarePurge::appendZone('zone-a');
        CloudflarePurge::appendZone('zone-a');

        $this->assertSame(['zone-a'], array_values(CloudflarePurge::getZones()));
    }

    #[Test]
    public function it_resolves_storage_path_and_current_zone_from_string(): void
    {
        $this->assertSame('test-zone', CloudflarePurge::getCurrentZone());
        $this->assertSame('/.cloudflare-invalidate-urls-test-zone', CloudflarePurge::getStoragePath());
        $this->assertSame('/.cloudflare-invalidate-urls-custom', CloudflarePurge::getStoragePath('custom'));
    }

    #[Test]
    public function it_resolves_current_zone_from_site_map(): void
    {
        config()->set('cloudflare-purge.zone', [
            'default' => 'zone-default',
            'fr' => 'zone-fr',
        ]);

        $site = $this->createMock(Site::class);
        $site->method('handle')->willReturn('fr');
        SiteFacade::shouldReceive('current')->andReturn($site);

        $this->assertSame('zone-fr', CloudflarePurge::getCurrentZone());
    }

    #[Test]
    public function it_returns_null_when_current_site_is_invalid(): void
    {
        config()->set('cloudflare-purge.zone', [
            'default' => 'zone-default',
        ]);

        SiteFacade::shouldReceive('current')->andReturn(new \stdClass);

        $this->assertNull(CloudflarePurge::getCurrentZone());
    }

    #[Test]
    public function it_returns_null_when_site_handle_is_not_a_string(): void
    {
        config()->set('cloudflare-purge.zone', [
            'default' => 'zone-default',
        ]);

        $site = $this->createMock(Site::class);
        $site->method('handle')->willReturn(123);
        SiteFacade::shouldReceive('current')->andReturn($site);

        $this->assertNull(CloudflarePurge::getCurrentZone());
    }

    #[Test]
    public function it_returns_null_for_unknown_site_handle(): void
    {
        config()->set('cloudflare-purge.zone', [
            'default' => 'zone-default',
        ]);

        $site = $this->createMock(Site::class);
        $site->method('handle')->willReturn('missing');
        SiteFacade::shouldReceive('current')->andReturn($site);

        $this->assertNull(CloudflarePurge::getCurrentZone());
        CloudflarePurge::appendZone();
        CloudflarePurge::removeZone();

        $this->assertSame([], array_values(CloudflarePurge::getZones()));
    }

    #[Test]
    public function it_resolves_current_zone_from_callable(): void
    {
        config()->set('cloudflare-purge.zone', fn (): string => 'callable-zone');

        $this->assertSame('callable-zone', CloudflarePurge::getCurrentZone());
    }

    #[Test]
    public function it_handles_missing_storage_files(): void
    {
        $this->assertSame([], array_values(CloudflarePurge::getInvalidateUrls('empty')));
        $this->assertSame([], array_values(CloudflarePurge::getZones()));
    }

    #[Test]
    public function it_appends_urls_to_the_zone_of_their_site(): void
    {
        $this->setUpMultisite();

        CloudflarePurge::appendInvalidateUrl('https://example.de/a');
        CloudflarePurge::appendInvalidateUrl('https://example.nl/b');
        CloudflarePurge::appendInvalidateUrl('https://example.de/a');

        $this->assertSame(['https://example.de/a'], array_values(CloudflarePurge::getInvalidateUrls('zone-de')));
        $this->assertSame(['https://example.nl/b'], array_values(CloudflarePurge::getInvalidateUrls('zone-nl')));
        $this->assertSame(['zone-de', 'zone-nl'], array_values(CloudflarePurge::getZones()));
    }

    #[Test]
    public function it_skips_urls_without_a_zone(): void
    {
        $this->setUpMultisite();

        CloudflarePurge::appendInvalidateUrl('https://unknown.com/a');
        CloudflarePurge::appendInvalidateUrl('https://example.fr/a');

        $this->assertSame([], array_values(CloudflarePurge::getInvalidateUrls('zone-nl')));
        $this->assertSame([], array_values(CloudflarePurge::getZones()));
    }

    #[Test]
    public function it_skips_urls_when_no_zone_is_configured(): void
    {
        config()->set('cloudflare-purge.zone', '');

        CloudflarePurge::appendInvalidateUrl('https://example.com/a');

        Storage::disk('local')->assertMissing('/.cloudflare-invalidate-urls-');
        $this->assertSame([], array_values(CloudflarePurge::getZones()));
    }

    #[Test]
    public function it_resolves_zone_by_url_from_site_map(): void
    {
        $this->setUpMultisite();

        $this->assertSame('zone-de', CloudflarePurge::getZoneByUrl('https://example.de/a'));
        $this->assertSame('zone-nl', CloudflarePurge::getZoneByUrl('https://example.nl/a'));
        $this->assertNull(CloudflarePurge::getZoneByUrl('https://example.fr/a'));
        $this->assertNull(CloudflarePurge::getZoneByUrl('https://unknown.com/a'));
    }

    #[Test]
    public function it_returns_null_zone_by_url_when_site_handle_is_not_a_string(): void
    {
        config()->set('cloudflare-purge.zone', [
            'default' => 'zone-default',
        ]);

        $site = $this->createStub(Site::class);
        $site->method('handle')->willReturn(123);
        SiteFacade::shouldReceive('findByUrl')->andReturn($site);

        $this->assertNull(CloudflarePurge::getZoneByUrl('https://example.com/a'));
    }

    #[Test]
    public function it_resolves_zone_by_url_from_string_and_callable(): void
    {
        $this->assertSame('test-zone', CloudflarePurge::getZoneByUrl('https://example.com/a'));

        config()->set('cloudflare-purge.zone', fn (): string => 'callable-zone');

        $this->assertSame('callable-zone', CloudflarePurge::getZoneByUrl('https://example.com/a'));
    }

    #[Test]
    public function it_resolves_configured_zones(): void
    {
        $this->assertSame(['test-zone'], CloudflarePurge::getConfiguredZones());

        config()->set('cloudflare-purge.zone', [
            'nl' => 'zone-nl',
            'be' => 'zone-nl',
            'de' => 'zone-de',
            'fr' => null,
            'it' => '',
        ]);

        $this->assertSame(['zone-nl', 'zone-de'], CloudflarePurge::getConfiguredZones());

        config()->set('cloudflare-purge.zone', fn (): string => 'callable-zone');

        $this->assertSame(['callable-zone'], CloudflarePurge::getConfiguredZones());

        config()->set('cloudflare-purge.zone', null);

        $this->assertSame([], CloudflarePurge::getConfiguredZones());
    }

    protected function setUpMultisite(): void
    {
        SiteFacade::setSites([
            'nl' => ['name' => 'NL', 'url' => 'https://example.nl/', 'locale' => 'nl_NL'],
            'de' => ['name' => 'DE', 'url' => 'https://example.de/', 'locale' => 'de_DE'],
            'fr' => ['name' => 'FR', 'url' => 'https://example.fr/', 'locale' => 'fr_FR'],
        ]);

        config()->set('cloudflare-purge.zone', [
            'nl' => 'zone-nl',
            'de' => 'zone-de',
        ]);
    }
}
