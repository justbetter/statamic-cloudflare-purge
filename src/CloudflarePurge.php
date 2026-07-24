<?php

namespace JustBetter\StatamicCloudflarePurge;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Statamic\Facades\Site as SiteFacade;
use Statamic\Sites\Site;

class CloudflarePurge
{
    /**
     * @return array<int, string>
     */
    public static function getInvalidateUrls(?string $zone = null): array
    {
        $files = explode("\n", Storage::disk('local')->get(static::getStoragePath($zone)) ?? '');

        /** @var array<int, string> */
        return Arr::where($files, fn (string $file): bool => $file !== '');
    }

    public static function clearInvalidateUrls(?string $zone = null): void
    {
        Storage::disk('local')->delete(static::getStoragePath($zone));

        static::removeZone($zone);
    }

    /**
     * @return array<int, string>
     */
    public static function popInvalidateUrls(?string $zone = null): array
    {
        $files = static::getInvalidateUrls($zone);

        static::clearInvalidateUrls($zone);

        return $files;
    }

    public static function appendInvalidateUrl(string $url, ?string $zone = null): void
    {
        if (in_array($url, static::getInvalidateUrls($zone), true)) {
            return;
        }

        Storage::disk('local')->append(static::getStoragePath($zone), $url);

        static::appendZone($zone);
    }

    public static function getStoragePath(?string $zone = null): string
    {
        if (! $zone) {
            $zone = static::getCurrentZone();
        }

        return '/.cloudflare-invalidate-urls-'.$zone;
    }

    public static function appendZone(?string $zone = null): void
    {
        if (! $zone) {
            $zone = static::getCurrentZone();
        }

        if ($zone === null || in_array($zone, static::getZones(), true)) {
            return;
        }

        Storage::disk('local')->append('/.cloudflare-invalidate-zones', $zone);
    }

    public static function removeZone(?string $zone = null): void
    {
        if (! $zone) {
            $zone = static::getCurrentZone();
        }

        if ($zone === null) {
            return;
        }

        $zones = static::getZones();
        $id = array_search($zone, $zones, true);

        if ($id === false) {
            return;
        }

        unset($zones[$id]);

        Storage::disk('local')->put('/.cloudflare-invalidate-zones', implode("\n", $zones));
    }

    /**
     * @return array<int, string>
     */
    public static function getZones(): array
    {
        $zones = explode("\n", Storage::disk('local')->get('/.cloudflare-invalidate-zones') ?? '');

        /** @var array<int, string> */
        return Arr::where($zones, fn (string $zone): bool => $zone !== '');
    }

    public static function getCurrentZone(): ?string
    {
        $zone = config('cloudflare-purge.zone');

        if (is_array($zone)) {
            $site = SiteFacade::current();

            if (! $site instanceof Site) {
                return null;
            }

            $handle = $site->handle();

            if (! is_string($handle)) {
                return null;
            }

            $resolved = $zone[$handle] ?? null;

            return is_string($resolved) ? $resolved : null;
        }

        $resolved = value($zone);

        return is_string($resolved) ? $resolved : null;
    }
}
