<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Sitemap\SitemapXml;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SitemapController extends Controller
{
    public function index(Request $request): Response
    {
        $pagesLastmod = $this->pagesLastModified();
        $productsLastmod = $this->productsLastModified();

        $cache = $this->cache();
        $cacheKey = 'sitemap:index:v1';
        $cacheTtl = $this->cacheTtlSeconds();
        $version = implode('|', [
            $pagesLastmod?->toAtomString() ?? '',
            $productsLastmod?->toAtomString() ?? '',
            (string) config('app.url'),
        ]);

        $payload = $cache->get($cacheKey);
        if (is_array($payload) && ($payload['version'] ?? null) === $version && is_string($payload['xml'] ?? null)) {
            return $this->xmlResponse($request, $payload['xml'], $this->maxLastMod([$pagesLastmod, $productsLastmod]));
        }

        $xml = SitemapXml::sitemapIndex([
            [
                'loc' => route('sitemaps.pages', absolute: true),
                'lastmod' => $pagesLastmod,
            ],
            [
                'loc' => route('sitemaps.products', absolute: true),
                'lastmod' => $productsLastmod,
            ],
        ]);

        $cache->put($cacheKey, ['version' => $version, 'xml' => $xml], $cacheTtl);

        return $this->xmlResponse($request, $xml, $this->maxLastMod([$pagesLastmod, $productsLastmod]));
    }

    public function pages(Request $request): Response
    {
        $lastmod = $this->pagesLastModified();
        $cache = $this->cache();
        $cacheKey = 'sitemap:pages:v1';

        $cacheTtl = $this->cacheTtlSeconds();
        $version = implode('|', [
            $lastmod?->toAtomString() ?? '',
            (string) config('app.url'),
        ]);

        $payload = $cache->get($cacheKey);
        if (is_array($payload) && ($payload['version'] ?? null) === $version && is_string($payload['xml'] ?? null)) {
            return $this->xmlResponse($request, $payload['xml'], $lastmod);
        }

        $entries = [];
        foreach ((array) config('sitemap.static_urls', []) as $item) {
            $path = isset($item['path']) ? trim((string) $item['path']) : '';
            if ($path === '') {
                continue;
            }

            $loc = url($path === '/' ? '/' : '/'.ltrim($path, '/'));

            $entryLastmod = $this->lastModifiedFromFiles((array) ($item['source_files'] ?? []));

            $entries[] = [
                'loc' => $loc,
                'lastmod' => $entryLastmod,
                'changefreq' => $item['changefreq'] ?? null,
                'priority' => $item['priority'] ?? null,
            ];
        }

        $xml = SitemapXml::urlset($entries, includeImages: false);

        $cache->put($cacheKey, ['version' => $version, 'xml' => $xml], $cacheTtl);

        return $this->xmlResponse($request, $xml, $lastmod);
    }

    public function products(Request $request): Response
    {
        $lastmod = $this->productsLastModified();
        $cache = $this->cache();
        $cacheKey = 'sitemap:products:v1';

        $cacheTtl = $this->cacheTtlSeconds();
        $version = implode('|', [
            $lastmod?->toAtomString() ?? '',
            (string) config('app.url'),
            (string) config('sitemap.include_images', true),
        ]);

        $payload = $cache->get($cacheKey);
        if (is_array($payload) && ($payload['version'] ?? null) === $version && is_string($payload['xml'] ?? null)) {
            return $this->xmlResponse($request, $payload['xml'], $lastmod);
        }

        $includeImages = (bool) config('sitemap.include_images', true);

        try {
            $query = Product::query()
                ->active()
                ->visibleToPublic()
                ->whereIn('product_type', ['standard', 'dimension_based'])
                ->select(['id', 'slug', 'name', 'updated_at']);

            if ($includeImages) {
                $query->with(['primaryImage:id,product_id,path']);
            }

            $entries = $query
                ->lazyById(500)
                ->map(function (Product $product) use ($includeImages) {
                    $images = null;

                    if ($includeImages) {
                        $imgUrl = $this->publicAssetUrl($product->primaryImage?->path);
                        if ($imgUrl) {
                            $images = [
                                [
                                    'loc' => $imgUrl,
                                    'title' => $product->name,
                                ],
                            ];
                        }
                    }

                    return [
                        'loc' => route('products.show', ['product' => $product->slug], absolute: true),
                        'lastmod' => $product->updated_at instanceof CarbonInterface ? $product->updated_at : null,
                        'changefreq' => 'weekly',
                        'priority' => 0.8,
                        'images' => $images,
                    ];
                });

            $xml = SitemapXml::urlset($entries, includeImages: $includeImages);
        } catch (\Throwable) {
            $xml = SitemapXml::urlset([], includeImages: $includeImages);
            $lastmod = null;
        }

        $cache->put($cacheKey, ['version' => $version, 'xml' => $xml], $cacheTtl);

        return $this->xmlResponse($request, $xml, $lastmod);
    }

    public function robots(Request $request): Response
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin/',
            'Disallow: /portal/',
            'Disallow: /cart/',
            'Disallow: /checkout/',
            'Disallow: /dashboard',
            'Disallow: /profile',
            'Disallow: /notifications',
            'Disallow: /ajax/',
            'Disallow: /auth/',
            'Disallow: /orders/secure/',
            'Disallow: /invoices/',
            'Disallow: /estimate/',
            '',
            'Sitemap: '.url('/sitemap.xml'),
            '',
        ];

        $body = implode("\n", $lines);

        return response($body, 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8')
            ->header('X-Content-Type-Options', 'nosniff');
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

    private function cacheTtlSeconds(): int
    {
        $ttl = (int) config('sitemap.cache_ttl_seconds', 3600);

        return max(60, min(86400, $ttl));
    }

    private function cache(): CacheRepository
    {
        $store = trim((string) config('sitemap.cache_store', ''));

        try {
            return $store !== '' ? Cache::store($store) : Cache::store();
        } catch (\Throwable) {
            return Cache::store();
        }
    }

    private function pagesLastModified(): ?CarbonImmutable
    {
        $max = $this->maxLastMod(array_map(
            fn (array $item) => $this->lastModifiedFromFiles((array) ($item['source_files'] ?? [])),
            (array) config('sitemap.static_urls', [])
        ));

        return $max instanceof CarbonImmutable ? $max : null;
    }

    private function productsLastModified(): ?CarbonImmutable
    {
        try {
            $value = Product::query()
                ->active()
                ->visibleToPublic()
                ->whereIn('product_type', ['standard', 'dimension_based'])
                ->max('updated_at');

            if (! $value) {
                return null;
            }

            return CarbonImmutable::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param  array<int,CarbonInterface|null>  $dates
     */
    private function maxLastMod(array $dates): ?CarbonImmutable
    {
        $max = null;

        foreach ($dates as $dt) {
            if (! $dt instanceof CarbonInterface) {
                continue;
            }

            $candidate = $dt instanceof CarbonImmutable ? $dt : CarbonImmutable::instance($dt->toDateTimeImmutable());
            $max = $max ? ($candidate->greaterThan($max) ? $candidate : $max) : $candidate;
        }

        return $max;
    }

    /**
     * @param  array<int,string>  $files
     */
    private function lastModifiedFromFiles(array $files): ?CarbonImmutable
    {
        $maxTs = null;

        foreach ($files as $file) {
            $file = trim((string) $file);
            if ($file === '' || ! is_file($file)) {
                continue;
            }

            $ts = @filemtime($file);
            if (! is_int($ts) || $ts <= 0) {
                continue;
            }

            $maxTs = $maxTs ? max($maxTs, $ts) : $ts;
        }

        return $maxTs ? CarbonImmutable::createFromTimestampUTC($maxTs) : null;
    }

    private function publicAssetUrl(?string $path): ?string
    {
        $path = $path ? trim((string) $path) : '';
        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/')) {
            return url($path);
        }

        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        return Storage::disk('public')->url($path);
    }
}
