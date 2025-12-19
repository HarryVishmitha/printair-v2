@php
    $enabled = filter_var(config('services.analytics.enabled', true), FILTER_VALIDATE_BOOLEAN);
    $ga4 = config('services.analytics.ga4_measurement_id') ?: 'G-P93Z5D5Y8F';
    $pixel = config('services.analytics.meta_pixel_id');
    $consentKey = 'printair_cookie_consent';
@endphp

@if ($enabled && (!empty($ga4) || !empty($pixel)))
    <script>
        (function() {
            const CONSENT_KEY = @json($consentKey);

            function hasConsent() {
                try {
                    return localStorage.getItem(CONSENT_KEY) === 'accepted';
                } catch (e) {
                    return false;
                }
            }

            function loadScript(src, {
                async = true,
                defer = false
            } = {}) {
                return new Promise((resolve, reject) => {
                    const s = document.createElement('script');
                    s.src = src;
                    s.async = async;
                    s.defer = defer;
                    s.onload = resolve;
                    s.onerror = reject;
                    document.head.appendChild(s);
                });
            }

            async function loadGA4(measurementId) {
                if (!measurementId) return;

                // Prevent double-load
                if (window.__printair_ga4_loaded) return;
                window.__printair_ga4_loaded = true;

                await loadScript('https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(
                measurementId));

                window.dataLayer = window.dataLayer || [];

                function gtag() {
                    dataLayer.push(arguments);
                }
                window.gtag = window.gtag || gtag;

                gtag('js', new Date());
                gtag('config', measurementId, {
                    anonymize_ip: true
                });
            }

            function loadMetaPixel(pixelId) {
                if (!pixelId) return;

                // Prevent double-load
                if (window.__printair_pixel_loaded) return;
                window.__printair_pixel_loaded = true;

                ! function(f, b, e, v, n, t, s) {
                    if (f.fbq) return;
                    n = f.fbq = function() {
                        n.callMethod ?
                            n.callMethod.apply(n, arguments) : n.queue.push(arguments)
                    };
                    if (!f._fbq) f._fbq = n;
                    n.push = n;
                    n.loaded = !0;
                    n.version = '2.0';
                    n.queue = [];
                    t = b.createElement(e);
                    t.async = !0;
                    t.src = v;
                    s = b.getElementsByTagName(e)[0];
                    s.parentNode.insertBefore(t, s)
                }(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');

                window.fbq('init', pixelId);
                window.fbq('track', 'PageView');
            }

            async function loadAnalytics() {
                try {
                    // GA4
                    @if (!empty($ga4))
                        await loadGA4(@json($ga4));
                    @endif
                } catch (e) {
                    console.warn('GA4 failed to load', e);
                }

                try {
                    // Meta Pixel
                    @if (!empty($pixel))
                        loadMetaPixel(@json($pixel));
                    @endif
                } catch (e) {
                    console.warn('Meta Pixel failed to load', e);
                }
            }

            // 1) Load immediately if already accepted
            if (hasConsent()) {
                loadAnalytics();
            }

            // 2) If user accepts later, load instantly
            window.addEventListener('printair:cookies-accepted', function() {
                loadAnalytics();
            });
        })();
    </script>
@endif
