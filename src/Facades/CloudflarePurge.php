<?php

namespace JustBetter\StatamicCloudflarePurge\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void appendInvalidateUrl(string $url)
 * @method static array getInvalidateUrls():
 * @method static array popInvalidateUrls():
 * @method static void clearInvalidateUrls():
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
