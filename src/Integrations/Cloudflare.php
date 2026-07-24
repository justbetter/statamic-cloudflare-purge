<?php

namespace JustBetter\StatamicCloudflarePurge\Integrations;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use JustBetter\StatamicCloudflarePurge\Exceptions\CloudflareException;

class Cloudflare
{
    public function http(): PendingRequest
    {
        $endpoint = config('cloudflare-purge.endpoint');
        $token = config('cloudflare-purge.token');

        return Http::baseUrl(is_string($endpoint) ? $endpoint : '')
            ->withToken(is_string($token) ? $token : '');
    }

    /**
     * @param  array<int, string>|null  $files
     * @param  array<int, string>|null  $tags
     * @param  array<int, string>|null  $hosts
     */
    public function purge(string $zone, ?array $files = null, ?array $tags = null, ?array $hosts = null, bool $everything = false): bool
    {
        /** @var array<string, mixed> $options */
        $options = [];

        if ($everything) {
            $options['purge_everything'] = true;
        } else {
            if ($files) {
                $rateLimit = max(1, config()->integer('cloudflare-purge.rate-limits.single-file.per-request', 100));
                if (count($files) > $rateLimit) {
                    foreach (array_chunk($files, $rateLimit) as $chunk) {
                        $this->purge($zone, $chunk);
                    }
                } else {
                    $options['files'] = $files;
                }
            }
            if ($tags) {
                $options['tags'] = $tags;
            }
            if ($hosts) {
                $options['hosts'] = $hosts;
            }
        }

        $options = Arr::where(
            $options,
            fn (mixed $option): bool => $option === true || (is_array($option) && count($option) > 0)
        );

        if (count($options) === 0) {
            return true;
        }

        if ($zone === '') {
            throw new CloudflareException('No zone ID');
        }

        /** @var array{success?: bool, errors?: list<array{code: int|string, message: string}>}|null $response */
        $response = $this->http()->post('zones/'.$zone.'/purge_cache', $options)->json();

        $success = $response['success'] ?? false;
        $errors = $response['errors'] ?? [];

        if (count($errors) > 0) {
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
