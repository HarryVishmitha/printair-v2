<?php

namespace App\Http\Controllers;

use App\Services\Gmc\GmcProductsFeedService;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class GmcFeedController extends Controller
{
    public function __construct(
        private readonly GmcProductsFeedService $feed,
    ) {
    }

    public function products(Request $request): Response
    {
        $debugKey = trim((string) config('gmc.debug_key', ''));
        $debugAllowed = $request->boolean('debug')
            && $debugKey !== ''
            && hash_equals($debugKey, (string) $request->query('key', ''));

        $cache = $this->cache();
        $cacheKey = 'gmc:products:v1';
        $payload = $cache->get($cacheKey);

        $lastmod = null;
        $version = null;

        try {
            $lastmod = $this->feed->lastModified();
            $version = implode('|', [
                $lastmod?->toAtomString() ?? '',
                (string) config('app.url'),
            ]);
        } catch (\Throwable $e) {
            if (is_array($payload) && is_string($payload['xml'] ?? null)) {
                return $this->xmlResponse($request, $payload['xml'], null);
            }

            if ($debugAllowed) {
                $meta = [
                    'service_version' => $this->feed->version(),
                    'service_has_legacy_pricings_builder_hint' => $this->feed->hasLegacyPricingsWithBuilderHint(),
                    'exception' => [
                        'class' => get_class($e),
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ],
                    'opcache' => function_exists('opcache_get_status') ? @opcache_get_status(false) : null,
                ];

                $body = "GMC feed debug (lastModified)\n\n".json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n";

                return response($body, 500)
                    ->header('Content-Type', 'text/plain; charset=UTF-8')
                    ->header('X-Gmc-Feed-Version', $this->feed->version());
            }

            return response('Feed temporarily unavailable.', 503)
                ->header('Content-Type', 'text/plain; charset=UTF-8')
                ->header('X-Gmc-Feed-Version', $this->feed->version())
                ->header('Retry-After', '300');
        }

        if (is_array($payload) && ($payload['version'] ?? null) === $version && is_string($payload['xml'] ?? null)) {
            return $this->xmlResponse($request, $payload['xml'], $lastmod);
        }

        try {
            if ($debugAllowed && function_exists('opcache_invalidate')) {
                @opcache_invalidate(app_path('Services/Gmc/GmcProductsFeedService.php'), true);
                @opcache_invalidate(__FILE__, true);
            }

            $xml = $this->feed->buildXml();
        } catch (\Throwable $e) {
            if (is_array($payload) && is_string($payload['xml'] ?? null)) {
                return $this->xmlResponse($request, $payload['xml'], $lastmod);
            }

            if ($debugAllowed || (bool) config('app.debug')) {
                $meta = [
                    'service_version' => $this->feed->version(),
                    'service_has_legacy_pricings_builder_hint' => $this->feed->hasLegacyPricingsWithBuilderHint(),
                    'exception' => [
                        'class' => get_class($e),
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ],
                    'opcache' => function_exists('opcache_get_status') ? @opcache_get_status(false) : null,
                ];

                $body = "GMC feed debug\n\n".json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n";

                return response($body, 500)
                    ->header('Content-Type', 'text/plain; charset=UTF-8')
                    ->header('X-Gmc-Feed-Version', $this->feed->version());
            }

            return response('Feed temporarily unavailable.', 503)
                ->header('Content-Type', 'text/plain; charset=UTF-8')
                ->header('X-Gmc-Feed-Version', $this->feed->version())
                ->header('Retry-After', '300');
        }

        $hasItems = str_contains($xml, '<item>');
        $cacheTtl = $hasItems ? 3600 : 60;
        $cache->put($cacheKey, ['version' => $version, 'xml' => $xml], $cacheTtl);

        $response = $this->xmlResponse($request, $xml, $lastmod);
        $response->headers->set('X-Gmc-Feed-Version', $this->feed->version());

        return $response;
    }

    private function xmlResponse(Request $request, string $xml, ?CarbonInterface $lastModified = null): Response
    {
        $response = response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('X-Content-Type-Options', 'nosniff');

        $response->setEtag('"'.sha1($xml).'"');
        $response->setPublic();

        if ($lastModified instanceof CarbonInterface) {
            $response->setLastModified($lastModified->toDateTimeImmutable());
        }

        $response->isNotModified($request);

        return $response;
    }

    private function cache(): CacheRepository
    {
        try {
            return Cache::store();
        } catch (\Throwable) {
            return Cache::store(config('cache.default'));
        }
    }
}
