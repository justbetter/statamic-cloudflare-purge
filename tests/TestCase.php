<?php

namespace JustBetter\StatamicCloudflarePurge\Tests;

use JustBetter\StatamicCloudflarePurge\StatamicCloudflarePurgeServiceProvider;
use Statamic\Testing\AddonTestCase;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

abstract class TestCase extends AddonTestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected string $addonServiceProvider = StatamicCloudflarePurgeServiceProvider::class;

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:7tG0yY7g3QkFrQ+Vk4EBSbcT8D9C4/5Dph1dNRjh6WU=');
        $app['config']->set('cache.default', 'array');
        $app['config']->set('cloudflare-purge.enabled', true);
        $app['config']->set('cloudflare-purge.endpoint', 'https://api.cloudflare.com/client/v4');
        $app['config']->set('cloudflare-purge.token', 'test-token');
        $app['config']->set('cloudflare-purge.zone', 'test-zone');
        $app['config']->set('cloudflare-purge.rate-limits.single-file.per-request', 100);
    }
}
