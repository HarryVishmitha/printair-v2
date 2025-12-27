<?php

namespace App\Services\Sitemap;

use Carbon\CarbonInterface;
use XMLWriter;

class SitemapXml
{
    /**
     * @param  iterable<array{loc:string,lastmod?:CarbonInterface|null,changefreq?:string|null,priority?:float|null,images?:array<int,array{loc:string,caption?:string|null,title?:string|null}>|null}>  $entries
     */
    public static function urlset(iterable $entries, bool $includeImages = false): string
    {
        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->startDocument('1.0', 'UTF-8');
        $writer->setIndent(true);

        $writer->startElement('urlset');
        $writer->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        if ($includeImages) {
            $writer->writeAttribute('xmlns:image', 'http://www.google.com/schemas/sitemap-image/1.1');
        }

        foreach ($entries as $entry) {
            $loc = (string) ($entry['loc'] ?? '');
            if ($loc === '') {
                continue;
            }

            $writer->startElement('url');
            $writer->writeElement('loc', $loc);

            $lastmod = $entry['lastmod'] ?? null;
            if ($lastmod instanceof CarbonInterface) {
                $writer->writeElement('lastmod', $lastmod->toAtomString());
            }

            $changefreq = isset($entry['changefreq']) ? trim((string) $entry['changefreq']) : '';
            if ($changefreq !== '') {
                $writer->writeElement('changefreq', $changefreq);
            }

            if (array_key_exists('priority', $entry) && $entry['priority'] !== null) {
                $priority = (float) $entry['priority'];
                $priority = max(0.0, min(1.0, $priority));
                $writer->writeElement('priority', number_format($priority, 1, '.', ''));
            }

            if ($includeImages && ! empty($entry['images']) && is_array($entry['images'])) {
                foreach ($entry['images'] as $img) {
                    $imgLoc = isset($img['loc']) ? trim((string) $img['loc']) : '';
                    if ($imgLoc === '') {
                        continue;
                    }

                    $writer->startElement('image:image');
                    $writer->writeElement('image:loc', $imgLoc);

                    $caption = isset($img['caption']) ? trim((string) $img['caption']) : '';
                    if ($caption !== '') {
                        $writer->writeElement('image:caption', $caption);
                    }

                    $title = isset($img['title']) ? trim((string) $img['title']) : '';
                    if ($title !== '') {
                        $writer->writeElement('image:title', $title);
                    }

                    $writer->endElement(); // image:image
                }
            }

            $writer->endElement(); // url
        }

        $writer->endElement(); // urlset
        $writer->endDocument();

        return $writer->outputMemory();
    }

    /**
     * @param  iterable<array{loc:string,lastmod?:CarbonInterface|null}>  $sitemaps
     */
    public static function sitemapIndex(iterable $sitemaps): string
    {
        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->startDocument('1.0', 'UTF-8');
        $writer->setIndent(true);

        $writer->startElement('sitemapindex');
        $writer->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($sitemaps as $sitemap) {
            $loc = (string) ($sitemap['loc'] ?? '');
            if ($loc === '') {
                continue;
            }

            $writer->startElement('sitemap');
            $writer->writeElement('loc', $loc);

            $lastmod = $sitemap['lastmod'] ?? null;
            if ($lastmod instanceof CarbonInterface) {
                $writer->writeElement('lastmod', $lastmod->toAtomString());
            }

            $writer->endElement(); // sitemap
        }

        $writer->endElement(); // sitemapindex
        $writer->endDocument();

        return $writer->outputMemory();
    }
}

