<?php

namespace JustBetter\StatamicCloudflarePurge\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void appendInvalidateUrl(string $url, ?string $zone = null)
 * @method static array getInvalidateUrls(?string $zone = null)
 * @method static array popInvalidateUrls(?string $zone = null)
 * @method static void clearInvalidateUrls(?string $zone = null)
 * @method static string getStoragePath(?string $zone = null)
 * @method static void appendZone(?string $zone = null)
 * @method static void removeZone(?string $zone = null)
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
