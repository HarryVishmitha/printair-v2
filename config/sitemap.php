<?php

return [
    // Cache store to use for sitemap endpoints.
    // Set to "file" to avoid database dependencies for public SEO endpoints.
    'cache_store' => env('SITEMAP_CACHE_STORE', 'file'),

    // Server-side caching (in seconds) for sitemap endpoints.
    'cache_ttl_seconds' => (int) env('SITEMAP_CACHE_TTL', 3600),

    // Add <image:image> entries for product pages when a primary image exists.
    'include_images' => (bool) env('SITEMAP_INCLUDE_IMAGES', true),

    // Static, indexable pages.
    // `source_files` are used to compute a stable `lastmod` based on file mtimes.
    'static_urls' => [
        [
            'path' => '/',
            'changefreq' => 'daily',
            'priority' => 1.0,
            'source_files' => [resource_path('views/home.blade.php')],
        ],
        [
            'path' => '/about-us',
            'changefreq' => 'monthly',
            'priority' => 0.7,
            'source_files' => [resource_path('views/about-us.blade.php')],
        ],
        [
            'path' => '/products',
            'changefreq' => 'daily',
            'priority' => 0.9,
            'source_files' => [resource_path('views/products/index.blade.php')],
        ],
        [
            'path' => '/services',
            'changefreq' => 'weekly',
            'priority' => 0.7,
            'source_files' => [resource_path('views/services/index.blade.php')],
        ],
        [
            'path' => '/contact',
            'changefreq' => 'yearly',
            'priority' => 0.5,
            'source_files' => [resource_path('views/pages/contact.blade.php')],
        ],
        [
            'path' => '/privacy-policy',
            'changefreq' => 'yearly',
            'priority' => 0.3,
            'source_files' => [resource_path('views/privacy.blade.php')],
        ],
        [
            'path' => '/terms-and-conditions',
            'changefreq' => 'yearly',
            'priority' => 0.3,
            'source_files' => [resource_path('views/terms-and-conditions.blade.php')],
        ],
        [
            'path' => '/B2B/partners',
            'changefreq' => 'monthly',
            'priority' => 0.4,
            'source_files' => [resource_path('views/partners.blade.php')],
        ],
    ],
];
