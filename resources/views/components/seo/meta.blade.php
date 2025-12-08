@props([
'title' => null,
'description' => null,
'keywords' => null,
'image' => null,
'url' => null,
'type' => 'website',
'noindex' => false,
])

@php
$siteName = config('seo.site_name');
$baseUrl = rtrim(config('seo.base_url'), '/');
$fullUrl = $url ? $url : url()->current();

$metaTitle = $title
? "{$title} | {$siteName}"
: config('seo.default_title');

$metaDescription = $description ?? config('seo.default_description');
$metaKeywords = $keywords ?? config('seo.default_keywords');

$imagePath = $image ?? config('seo.default_image');
$metaImage = Str::startsWith($imagePath, ['http://', 'https://'])
? $imagePath
: $baseUrl . $imagePath;

$robots = $noindex ? 'noindex, nofollow' : 'index, follow';
@endphp

<title>{{ $metaTitle }}</title>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">

<meta name="description" content="{{ $metaDescription }}">
@if($metaKeywords)
<meta name="keywords" content="{{ $metaKeywords }}">
@endif
<meta name="author" content="{{ $siteName }}">
<meta name="robots" content="{{ $robots }}">

<link rel="canonical" href="{{ $fullUrl }}">

{{-- Open Graph --}}
<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:image" content="{{ $metaImage }}">
<meta property="og:url" content="{{ $fullUrl }}">
<meta property="og:type" content="{{ $type }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:locale" content="{{ config('seo.locale') }}">

{{-- Twitter Card --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $metaTitle }}">
<meta name="twitter:description" content="{{ $metaDescription }}">
<meta name="twitter:image" content="{{ $metaImage }}">
@if(config('seo.twitter_handle'))
<meta name="twitter:site" content="{{ config('seo.twitter_handle') }}">
@endif

{{-- Progressive theming --}}
<meta name="theme-color" content="#b71c1c">
<meta name="color-scheme" content="light">

{{-- Favicons (adjust paths as needed) --}}
<link rel="icon" href="{{ asset('assets/printair/favicon.ico') }}" type="image/x-icon">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/printair/favicon.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/printair/favicon.png') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/printair/favicon.png') }}">
{{-- <link rel="manifest" href="{{ asset('assets/printair/site.webmanifest') }}"> --}}
{{-- Base Organization + Website schema --}}
<script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "Organization",
        "name": "{{ $siteName }}",
        "url": "{{ $baseUrl }}",
        "logo": "{{ $baseUrl }}/assets/logo.webp"
    }
</script>

<script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "WebSite",
        "name": "{{ $siteName }}",
        "url": "{{ $baseUrl }}",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "{{ $baseUrl }}/search?q={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
</script>

{{-- Page-level extra schemas (FAQ, Product, Breadcrumbs, etc.) --}}
@stack('structured-data')
