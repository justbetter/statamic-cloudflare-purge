<?php

namespace JustBetter\StatamicCloudflarePurge\Integrations;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use JustBetter\StatamicCloudflarePurge\Exceptions\CloudflareException;

class Cloudflare
{
    public function http(): PendingRequest
    {
        return Http::baseUrl(config('cloudflare-purge.endpoint'))
            ->withToken(config('cloudflare-purge.token'));
    }

    public function purge(string $zone, ?array $files = null, ?array $tags = null, ?array $hosts = null, bool $everything = false): bool
    {
        $options = [];

        if ($everything) {
            $options['purge_everything'] = true;
        } else {
            if ($files) {
                $options['files'] = $files;
            }
            if ($tags) {
                $options['tags'] = $tags;
            }
            if ($hosts) {
                $options['hosts'] = $hosts;
            }
        }

        // No need to continue when there's nothing to purge
        if (! count($options)) {
            return true;
        }

        if (! $zone) {
            throw new CloudflareException('No zone ID');
        }

        $response = $this->http()->post('zones/'.$zone.'/purge_cache', $options)->json();

        $success = $response['success'] ?? false;
        $errors = $response['errors'] ?? [];

        if (count($errors)) {
            $errorString = collect($errors)
                ->map(fn (array $error): string => "{$error['code']}: {$error['message']}")
                ->join("\n");

            throw new CloudflareException($errorString);
        }

        return $success;
    }

    public function purgeEverything(string $zone): bool
    {
        return $this->purge($zone, everything: true);
    }
}
