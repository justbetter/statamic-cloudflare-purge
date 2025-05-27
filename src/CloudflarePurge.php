<?php

namespace JustBetter\StatamicCloudflarePurge;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Statamic\Facades\Site;

class CloudflarePurge
{
    public static function getInvalidateUrls(?string $zone = null): array
    {
        $files = explode("\n", Storage::disk('local')->get(static::getStoragePath($zone)) ?? '');

        return Arr::where($files, fn ($file) => $file);
    }

    public static function clearInvalidateUrls(?string $zone = null): void
    {
        Storage::disk('local')->delete(static::getStoragePath($zone));

        static::removeZone($zone);
    }

    public static function popInvalidateUrls(?string $zone = null): array
    {
        $files = static::GetInvalidateUrls($zone);

        static::ClearInvalidateUrls($zone);

        return $files;
    }

    public static function appendInvalidateUrl(string $url, ?string $zone = null): void
    {
        if (in_array($url, static::GetInvalidateUrls($zone))) {
            return;
        }

        Storage::disk('local')->append(static::getStoragePath($zone), $url);
        
        static::appendZone($zone);
    }

    public static function getStoragePath(?string $zone = null): string
    {
        if (!$zone) {
            $zone = static::getCurrentZone();
        }
        
        return '/.cloudflare-invalidate-urls-' . $zone;
    }

    public static function appendZone(?string $zone = null): void
    {
        if (!$zone) {
            $zone = static::getCurrentZone();
        }

        if (in_array($zone, static::getZones())) {
            return;
        }

        Storage::disk('local')->append('/.cloudflare-invalidate-zones', $zone);
    }

    public static function removeZone(?string $zone = null): void
    {
        if (!$zone) {
            $zone = static::getCurrentZone();
        }

        $zones = static::getZones();
        $id = array_search($zone, $zones);

        if ($id === false) {
            return;
        }

        unset($zones[$id]);

        Storage::disk('local')->put('/.cloudflare-invalidate-zones', implode("\n", $zones));
    }

    public static function getZones(): array
    {
        $zones = explode("\n", Storage::disk('local')->get('/.cloudflare-invalidate-zones') ?? '');

        return Arr::where($zones, fn ($zone) => $zone);
    }

    public static function getCurrentZone(): ?string
    {
        $zone = config('cloudflare-purge.zone');

        if (is_array($zone)) {
            return $zone[Site::current()->handle];
        }

        return value($zone);
    }
}
