<?php

namespace JustBetter\StatamicCloudflarePurge\Tests\Integrations;

use Illuminate\Support\Facades\Http;
use JustBetter\StatamicCloudflarePurge\Exceptions\CloudflareException;
use JustBetter\StatamicCloudflarePurge\Integrations\Cloudflare;
use JustBetter\StatamicCloudflarePurge\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CloudflareTest extends TestCase
{
    #[Test]
    public function it_purges_files(): void
    {
        Http::fake([
            'api.cloudflare.com/*' => Http::response(['success' => true, 'errors' => []]),
        ]);

        $result = (new Cloudflare)->purge('zone-1', files: ['https://example.com']);

        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.cloudflare.com/client/v4/zones/zone-1/purge_cache'
                && $request['files'] === ['https://example.com'];
        });
    }

    #[Test]
    public function it_purges_tags_and_hosts(): void
    {
        Http::fake([
            'api.cloudflare.com/*' => Http::response(['success' => true, 'errors' => []]),
        ]);

        $this->assertTrue((new Cloudflare)->purge('zone-1', tags: ['tag-a'], hosts: ['example.com']));

        Http::assertSent(fn ($request) => $request['tags'] === ['tag-a'] && $request['hosts'] === ['example.com']);
    }

    #[Test]
    public function it_purges_everything(): void
    {
        Http::fake([
            'api.cloudflare.com/*' => Http::response(['success' => true, 'errors' => []]),
        ]);

        $this->assertTrue((new Cloudflare)->purgeEverything('zone-1'));

        Http::assertSent(fn ($request) => ($request['purge_everything'] ?? false) === true);
    }

    #[Test]
    public function it_chunks_large_file_lists(): void
    {
        config()->set('cloudflare-purge.rate-limits.single-file.per-request', 2);

        Http::fake([
            'api.cloudflare.com/*' => Http::response(['success' => true, 'errors' => []]),
        ]);

        $files = ['https://a.test', 'https://b.test', 'https://c.test'];

        $this->assertTrue((new Cloudflare)->purge('zone-1', files: $files));

        Http::assertSentCount(2);
    }

    #[Test]
    public function it_returns_true_when_there_is_nothing_to_purge(): void
    {
        Http::fake();

        $this->assertTrue((new Cloudflare)->purge('zone-1'));

        Http::assertNothingSent();
    }

    #[Test]
    public function it_throws_when_zone_is_empty(): void
    {
        $this->expectException(CloudflareException::class);
        $this->expectExceptionMessage('No zone ID');

        (new Cloudflare)->purge('', files: ['https://example.com']);
    }

    #[Test]
    public function it_throws_when_cloudflare_returns_errors(): void
    {
        Http::fake([
            'api.cloudflare.com/*' => Http::response([
                'success' => false,
                'errors' => [
                    ['code' => 1000, 'message' => 'Nope'],
                    ['code' => 1001, 'message' => 'Also nope'],
                ],
            ]),
        ]);

        $this->expectException(CloudflareException::class);
        $this->expectExceptionMessage("1000: Nope\n1001: Also nope");

        (new Cloudflare)->purge('zone-1', files: ['https://example.com']);
    }

    #[Test]
    public function it_returns_false_when_success_is_missing(): void
    {
        Http::fake([
            'api.cloudflare.com/*' => Http::response(['errors' => []]),
        ]);

        $this->assertFalse((new Cloudflare)->purge('zone-1', files: ['https://example.com']));
    }
}
