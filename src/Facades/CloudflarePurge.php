<?php

namespace JustBetter\StatamicCloudflarePurge\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void appendInvalidateUrl(string $url, ?string $zone = null)
 * @method static array<int, string> getInvalidateUrls(?string $zone = null)
 * @method static array<int, string> popInvalidateUrls(?string $zone = null)
 * @method static void clearInvalidateUrls(?string $zone = null)
 * @method static string getStoragePath(?string $zone = null)
 * @method static void appendZone(?string $zone = null)
 * @method static void removeZone(?string $zone = null)
 * @method static array<int, string> getZones()
 * @method static string|null getCurrentZone()
 *
 * @see \JustBetter\StatamicCloudflarePurge\CloudflarePurge
 */
class CloudflarePurge extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \JustBetter\StatamicCloudflarePurge\CloudflarePurge::class;
    }
}
