<?php

namespace JustBetter\StatamicCloudflarePurge\Tests\Exceptions;

use JustBetter\StatamicCloudflarePurge\Exceptions\CloudflareException;
use JustBetter\StatamicCloudflarePurge\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CloudflareExceptionTest extends TestCase
{
    #[Test]
    public function it_is_an_exception(): void
    {
        $exception = new CloudflareException('boom');

        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertSame('boom', $exception->getMessage());
    }
}
