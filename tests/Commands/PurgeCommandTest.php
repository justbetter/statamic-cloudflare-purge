<?php

namespace JustBetter\StatamicCloudflarePurge\Tests\Commands;

use Illuminate\Support\Facades\Bus;
use Illuminate\Testing\PendingCommand;
use JustBetter\StatamicCloudflarePurge\Jobs\PurgeCloudflareCachesJob;
use JustBetter\StatamicCloudflarePurge\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use ReflectionProperty;

class PurgeCommandTest extends TestCase
{
    #[Test]
    public function it_fails_when_purging_is_disabled(): void
    {
        config()->set('cloudflare-purge.enabled', false);

        Bus::fake();

        $command = $this->artisan('statamic:cloudflare:purge');
        $this->assertInstanceOf(PendingCommand::class, $command);

        $command
            ->expectsOutput('Purging has been disabled.')
            ->assertFailed();

        Bus::assertNothingDispatched();
    }

    #[Test]
    public function it_dispatches_the_purge_job(): void
    {
        Bus::fake();

        $command = $this->artisan('statamic:cloudflare:purge');
        $this->assertInstanceOf(PendingCommand::class, $command);
        $this->assertSame(0, $command->run());

        Bus::assertDispatched(PurgeCloudflareCachesJob::class, function (PurgeCloudflareCachesJob $job): bool {
            return (new ReflectionProperty(PurgeCloudflareCachesJob::class, 'all'))->getValue($job) === false;
        });
    }

    #[Test]
    public function it_dispatches_purge_everything_when_all_option_is_passed(): void
    {
        Bus::fake();

        $command = $this->artisan('statamic:cloudflare:purge', ['--all' => true]);
        $this->assertInstanceOf(PendingCommand::class, $command);
        $this->assertSame(0, $command->run());

        Bus::assertDispatched(PurgeCloudflareCachesJob::class, function (PurgeCloudflareCachesJob $job): bool {
            return (new ReflectionProperty(PurgeCloudflareCachesJob::class, 'all'))->getValue($job) === true;
        });
    }
}
