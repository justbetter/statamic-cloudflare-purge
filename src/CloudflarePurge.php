<?php

namespace JustBetter\StatamicCloudflarePurge;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class CloudflarePurge
{
    public static function getInvalidateUrls()
    {
        $files = explode("\n", Storage::disk('local')->get('/.cloudflare-invalidate-urls'));
        return Arr::where($files, fn ($file) => $file);
    }

    public static function clearInvalidateUrls()
    {
        Storage::disk('local')->delete('/.cloudflare-invalidate-urls');
    }

    public static function popInvalidateUrls()
    {
        $files = static::GetInvalidateUrls();

        static::ClearInvalidateUrls();

        return $files;
    }

    public static function appendInvalidateUrl(string $url)
    {
        if (in_array($url, static::GetInvalidateUrls())) {
            return;
        }

        Storage::disk('local')->append('/.cloudflare-invalidate-urls', $url);
    }
}