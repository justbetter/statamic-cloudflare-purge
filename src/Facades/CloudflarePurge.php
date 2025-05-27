<?php

namespace JustBetter\StatamicCloudflarePurge\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void appendInvalidateUrl(string $url, ?string $zone)
 * @method static array getInvalidateUrls(?string $zone)
 * @method static array popInvalidateUrls(?string $zone)
 * @method static void clearInvalidateUrls(?string $zone)
 * @method static string getStoragePath(?string $zone)
 * @method static void appendZone(?string $zone)
 * @method static void removeZone(?string $zone)
 * @method static array getZones()
 * @method static string getCurrentZone()
 *
 * @see \JustBetter\StatamicCloudflarePurge\CloudflarePurge
 */
class CloudflarePurge extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \JustBetter\StatamicCloudflarePurge\CloudflarePurge::class;
    }
}
