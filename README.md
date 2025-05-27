# Statamic Cloudflare Purge

This addon will hook into your already existing statamic invalidation and purge any pages that have been invalidated from your Cloudflare cache.

## Installation

```sh
composer require justbetter/statamic-cloudflare-purge
```

## Usage

You need a Cloudflare API key with the `Zone.Cache Purge` permission, and set it in your `.env`:

```dotenv
CLOUDFLARE_API_TOKEN="token_here"
```

You will also have to define the zone of your website:

```dotenv
CLOUDFLARE_ZONE="zone_id_here"
```

Finally, enable the package:

```dotenv
CLOUDFLARE_PURGING_ENABLED=true
```

If you have a multistore setup with multiple zones, see the [Configuration](#configuration) section.

This package listens to the `UrlInvalidated` event and adds every invalidated URL to a temp file. It also listens to certain events as defined in the config file to flush the whole cache.

Then, when you run the `statamic:cloudflare:purge` command or the `PurgeCloudflareCachesJob` job, these files will get purged from the Cloudflare cache. As such, you should add this to your `routes/console.php` like so:

```php
Schedule::job(\JustBetter\StatamicCloudflarePurge\Jobs\PurgeCloudflareCachesJob::class)->everyFiveSeconds()->withoutOverlapping();
```

> [!NOTE]
> Be aware of the [rate limits on the API](https://developers.cloudflare.com/cache/how-to/purge-cache/#availability-and-limits). You're *probably* not going to run into any issues, but it's possible. Especially if you end up calling an everything-purge often and you're on a free plan, or have a lot of sites running on the same Cloudflare account.

## Configuration

You can publish the config with the following command:

```sh
php artisan vendor:publish --provider="JustBetter\StatamicCloudflarePurge\StatamicCloudflarePurgeServiceProvider"
```

### Multiple zones

Using the configuration file you can define multiple zones. There are 3 ways of defining the zone in your config:

```php
// Single zone
'zone' => 'zone_id',
```

```php
// Multiple zones based on statamic site handles
'zone' => [
    'default' => 'zone_id_default',
    'french' => 'zone_id_french',
    ...
],
```

```php
// Complete freedom with a callback
'zone' => function() {
    return \App\Facades\Custom::getCloudflareZone()
},
```

### Cache flushing

You can define any events that will trigger a full cache purge immediately in the config file. By default, these three events have been defined for this purpose:

```php
'flush-events' => [
    \Statamic\Events\GlobalSetSaved::class,
    \Statamic\Events\NavSaved::class,
    \Statamic\Events\StaticCacheCleared::class,
],
```
