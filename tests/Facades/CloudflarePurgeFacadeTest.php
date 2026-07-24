<?php

namespace JustBetter\StatamicCloudflarePurge\Tests\Facades;

use Illuminate\Support\Facades\Storage;
use JustBetter\StatamicCloudflarePurge\Facades\CloudflarePurge;
use JustBetter\StatamicCloudflarePurge\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CloudflarePurgeFacadeTest extends TestCase
{
    #[Test]
    public function it_proxies_to_the_underlying_class(): void
    {
        Storage::fake('local');

        CloudflarePurge::appendInvalidateUrl('https://example.com');

        $this->assertSame(
            ['https://example.com'],
            array_values(CloudflarePurge::getInvalidateUrls())
        );
    }
}
