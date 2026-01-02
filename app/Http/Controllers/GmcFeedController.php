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
        $lastmod = $this->feed->lastModified();

        $cache = $this->cache();
        $cacheKey = 'gmc:products:v1';
        $cacheTtl = 3600;
        $version = implode('|', [
            $lastmod?->toAtomString() ?? '',
            (string) config('app.url'),
        ]);

        $payload = $cache->get($cacheKey);
        if (is_array($payload) && ($payload['version'] ?? null) === $version && is_string($payload['xml'] ?? null)) {
            return $this->xmlResponse($request, $payload['xml'], $lastmod);
        }

        $xml = $this->feed->buildXml();

        $cache->put($cacheKey, ['version' => $version, 'xml' => $xml], $cacheTtl);

        return $this->xmlResponse($request, $xml, $lastmod);
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
