<?php

namespace JustBetter\StatamicCloudflarePurge\Tests\Jobs;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use JustBetter\StatamicCloudflarePurge\CloudflarePurge;
use JustBetter\StatamicCloudflarePurge\Integrations\Cloudflare;
use JustBetter\StatamicCloudflarePurge\Jobs\PurgeCloudflareCachesJob;
use JustBetter\StatamicCloudflarePurge\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PurgeCloudflareCachesJobTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    #[Test]
    public function it_does_nothing_when_disabled(): void
    {
        config()->set('cloudflare-purge.enabled', false);

        Http::fake();
        CloudflarePurge::appendInvalidateUrl('https://example.com', 'zone-1');

        (new PurgeCloudflareCachesJob)->handle(new Cloudflare);

        Http::assertNothingSent();
        $this->assertSame(['https://example.com'], array_values(CloudflarePurge::getInvalidateUrls('zone-1')));
    }

    #[Test]
    public function it_purges_queued_urls_per_zone(): void
    {
        Http::fake([
            'api.cloudflare.com/*' => Http::response(['success' => true, 'errors' => []]),
        ]);

        CloudflarePurge::appendInvalidateUrl('https://example.com/a', 'zone-1');
        CloudflarePurge::appendInvalidateUrl('https://example.com/b', 'zone-2');

        (new PurgeCloudflareCachesJob)->handle(new Cloudflare);

        Http::assertSentCount(2);
        $this->assertSame([], array_values(CloudflarePurge::getZones()));
    }

    #[Test]
    public function it_can_purge_everything(): void
    {
        Http::fake([
            'api.cloudflare.com/*' => Http::response(['success' => true, 'errors' => []]),
        ]);

        CloudflarePurge::appendInvalidateUrl('https://example.com/a', 'zone-1');

        (new PurgeCloudflareCachesJob(true))->handle(new Cloudflare);

        Http::assertSent(fn ($request) => ($request['purge_everything'] ?? false) === true);
    }
}
