<?php

namespace App\Services\Gmc;

use App\DTO\Pricing\ResolvedPricing;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductPricing;
use App\Models\ProductVariantPricing;
use App\Models\ProductVariantSet;
use App\Models\ProductWorkingGroupOverride;
use App\Models\WorkingGroup;
use App\Services\Pricing\PricingResolverService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use XMLWriter;

class GmcProductsFeedService
{
    public function __construct(
        private readonly PricingResolverService $pricing,
    ) {
    }

    public function lastModified(): ?CarbonImmutable
    {
        try {
            $publicWgId = WorkingGroup::getPublicId();

            $productScope = function (Builder $q) use ($publicWgId): void {
                $q->active()
                    ->visibleToPublic()
                    ->whereIn('product_type', ['standard', 'dimension_based'])
                    ->whereHas('publicPricing');

                if ($publicWgId) {
                    $q->whereDoesntHave('workingGroupOverrides', function (Builder $qq) use ($publicWgId) {
                        $qq->where('working_group_id', $publicWgId)
                            ->where('is_visible', false)
                            ->whereNull('deleted_at');
                    });
                }
            };

            $dates = [];

            $productQuery = Product::query();
            $productScope($productQuery);
            $productMax = $this->parseDateMax($productQuery->max('updated_at'));
            if ($productMax) {
                $dates[] = $productMax;
            }

            $pricingMax = $this->parseDateMax(ProductPricing::query()
                ->public()
                ->active()
                ->whereHas('product', $productScope)
                ->max('updated_at'));
            if ($pricingMax) {
                $dates[] = $pricingMax;
            }

            $imageMax = $this->parseDateMax(ProductImage::query()
                ->whereHas('product', $productScope)
                ->max('updated_at'));
            if ($imageMax) {
                $dates[] = $imageMax;
            }

            $variantSetMax = $this->parseDateMax(ProductVariantSet::query()
                ->whereHas('product', $productScope)
                ->max('updated_at'));
            if ($variantSetMax) {
                $dates[] = $variantSetMax;
            }

            $variantPricingMax = $this->parseDateMax(ProductVariantPricing::query()
                ->whereHas('pricing', function (Builder $q) use ($productScope) {
                    $q->public()->active()->whereHas('product', $productScope);
                })
                ->max('updated_at'));
            if ($variantPricingMax) {
                $dates[] = $variantPricingMax;
            }

            if ($publicWgId) {
                $overrideMax = $this->parseDateMax(ProductWorkingGroupOverride::query()
                    ->where('working_group_id', $publicWgId)
                    ->whereHas('product', $productScope)
                    ->max('updated_at'));
                if ($overrideMax) {
                    $dates[] = $overrideMax;
                }
            }

            if (empty($dates)) {
                return null;
            }

            $max = null;
            foreach ($dates as $dt) {
                $max = $max ? ($dt->greaterThan($max) ? $dt : $max) : $dt;
            }

            return $max;
        } catch (\Throwable) {
            return null;
        }
    }

    public function buildXml(): string
    {
        try {
            $publicWgId = WorkingGroup::getPublicId();

            $query = Product::query()
                ->active()
                ->visibleToPublic()
                ->whereIn('product_type', ['standard', 'dimension_based'])
                ->whereHas('publicPricing')
                ->select(['id', 'product_code', 'name', 'slug', 'product_type', 'short_description', 'description', 'min_qty', 'updated_at', 'created_at']);

            if ($publicWgId) {
                $query->whereDoesntHave('workingGroupOverrides', function (Builder $qq) use ($publicWgId) {
                    $qq->where('working_group_id', $publicWgId)
                        ->where('is_visible', false)
                        ->whereNull('deleted_at');
                });
            }

            $query->with([
                'primaryImage:id,product_id,path',
                'publicPricing',
                'publicPricing.tiers',
                'publicPricing.variantPricings',
                'activeVariantSets:id,product_id,code,is_active,updated_at',
                'activeVariantSets.items.option:id,option_group_id,label',
                'activeVariantSets.items.option.group:id,name',
            ]);

            $writer = $this->startFeedWriter();

            foreach ($query->lazyById(200) as $product) {
                $publicPricing = $product->publicPricing;
                if (! $publicPricing instanceof ProductPricing) {
                    continue;
                }

                $rp = new ResolvedPricing(
                    effectivePricing: $publicPricing,
                    publicPricing: $publicPricing,
                    workingGroupPricing: null,
                    usingWorkingGroupOverride: false,
                    meta: ['product_id' => $product->id],
                );

                if ($product->product_type === 'dimension_based') {
                    $unit = $this->priceForDimensionBasedProduct($rp);
                    if ($unit === null) {
                        continue;
                    }

                    $this->writeItem(
                        $writer,
                        id: (string) $product->product_code,
                        title: (string) $product->name,
                        description: $this->descriptionFor($product->description, $product->short_description, $product->name),
                        link: route('products.show', ['product' => $product->slug], absolute: true),
                        imageLink: $this->publicAssetUrl($product->primaryImage?->path),
                        availabilityDate: $this->availabilityDateForProduct($product),
                        priceLkr: $unit,
                        mpn: (string) $product->product_code,
                        itemGroupId: null,
                    );

                    continue;
                }

                $base = $this->priceForStandardProduct($product, $rp);
                $variantSets = $product->activeVariantSets;

                if (! $variantSets || $variantSets->isEmpty()) {
                    if ($base === null) {
                        continue;
                    }

                    $this->writeItem(
                        $writer,
                        id: (string) $product->product_code,
                        title: (string) $product->name,
                        description: $this->descriptionFor($product->description, $product->short_description, $product->name),
                        link: route('products.show', ['product' => $product->slug], absolute: true),
                        imageLink: $this->publicAssetUrl($product->primaryImage?->path),
                        availabilityDate: $this->availabilityDateForProduct($product),
                        priceLkr: $base,
                        mpn: (string) $product->product_code,
                        itemGroupId: null,
                    );

                    continue;
                }

                foreach ($variantSets as $set) {
                    $variantLabel = $this->variantLabelForSet($set);
                    $vp = $this->pricing->variantPricing($rp, (int) $set->id);

                    $variantUnit = null;

                    if ($base !== null) {
                        $variantUnit = $base;
                    }

                    if ($vp && $vp->fixed_price !== null) {
                        $variantUnit = (string) ((float) ($variantUnit ?? 0.0) + (float) $vp->fixed_price);
                    }

                    if ($variantUnit === null) {
                        continue;
                    }

                    $title = $product->name.($variantLabel !== '' ? (' - '.$variantLabel) : '');
                    $itemId = $product->product_code.':v'.$set->id;

                    $this->writeItem(
                        $writer,
                        id: $itemId,
                        title: $title,
                        description: $this->descriptionFor($product->description, $product->short_description, $product->name),
                        link: route('products.show', ['product' => $product->slug], absolute: true),
                        imageLink: $this->publicAssetUrl($product->primaryImage?->path),
                        availabilityDate: $this->availabilityDateForProduct($product),
                        priceLkr: $variantUnit,
                        mpn: $itemId,
                        itemGroupId: (string) $product->product_code,
                    );
                }
            }

            $writer->endElement(); // channel
            $writer->endElement(); // rss
            $writer->endDocument();

            return $writer->outputMemory();
        } catch (\Throwable) {
            return $this->emptyFeedXml();
        }
    }

    private function priceForStandardProduct(Product $product, ResolvedPricing $rp): ?string
    {
        $minQty = is_numeric($product->min_qty) ? (int) $product->min_qty : 1;
        $qty = max(1, $minQty);

        $unit = $this->pricing->baseUnitPrice($rp, $qty);

        if ($unit === null) {
            return null;
        }

        return $unit;
    }

    private function priceForDimensionBasedProduct(ResolvedPricing $rp): ?string
    {
        $rates = $this->pricing->dimensionRates($rp);

        $minCharge = $rates['min_charge'] ?? null;
        if ($minCharge === null) {
            return null;
        }

        return (string) $minCharge;
    }

    private function variantLabelForSet(ProductVariantSet $set): string
    {
        $items = $set->items ?? collect();
        if ($items->isEmpty()) {
            return '';
        }

        $labels = $items
            ->map(fn ($it) => [
                'group' => (string) ($it->option?->group?->name ?? ''),
                'label' => (string) ($it->option?->label ?? ''),
            ])
            ->filter(fn ($row) => $row['label'] !== '')
            ->sortBy(fn ($row) => ($row['group'] !== '' ? $row['group'] : 'ZZZ').'|'.$row['label'])
            ->map(fn ($row) => $row['label'])
            ->values()
            ->all();

        return implode(' / ', $labels);
    }

    private function writeItem(
        XMLWriter $writer,
        string $id,
        string $title,
        string $description,
        string $link,
        ?string $imageLink,
        string $availabilityDate,
        string $priceLkr,
        string $mpn,
        ?string $itemGroupId,
    ): void {
        $id = trim($id);
        $title = $this->titleFor($title);
        $description = trim($description);
        $link = trim($link);
        $imageLink = $imageLink ? trim($imageLink) : null;
        $availabilityDate = trim($availabilityDate);
        $mpn = trim($mpn);

        $price = $this->formatPriceLkr($priceLkr);

        if ($id === '' || $title === '' || $description === '' || $link === '' || ! $imageLink || $price === null || $availabilityDate === '') {
            return;
        }

        $writer->startElement('item');

        $writer->writeElement('g:id', $id);
        $writer->writeElement('title', $title);
        $writer->writeElement('description', $description);
        $writer->writeElement('link', $link);

        if ($imageLink) {
            $writer->writeElement('g:image_link', $imageLink);
        }

        $writer->writeElement('g:availability', 'in stock');
        $writer->writeElement('g:availability_date', $availabilityDate);
        $writer->writeElement('g:condition', 'new');
        $writer->writeElement('g:brand', 'Printair');
        $writer->writeElement('g:mpn', $mpn);

        $writer->writeElement('g:price', $price);

        if ($itemGroupId) {
            $writer->writeElement('g:item_group_id', $itemGroupId);
        }

        $writer->endElement(); // item
    }

    private function descriptionFor(?string $description, ?string $shortDescription, string $fallbackName): string
    {
        $value = $description ?? $shortDescription ?? $fallbackName;
        $value = trim(Str::of($value)->stripTags()->replace("\u{00A0}", ' ')->squish()->toString());
        $value = $value !== '' ? mb_substr($value, 0, 5000) : '';

        return $value !== '' ? $value : $fallbackName;
    }

    private function titleFor(string $title): string
    {
        $title = trim(Str::of($title)->stripTags()->replace("\u{00A0}", ' ')->squish()->toString());
        if ($title === '') {
            return '';
        }

        return mb_substr($title, 0, 150);
    }

    private function availabilityDateForProduct(Product $product): string
    {
        try {
            return $product->updated_at ? CarbonImmutable::parse($product->updated_at)->toAtomString() : now()->toAtomString();
        } catch (\Throwable) {
            return now()->toAtomString();
        }
    }

    private function formatPriceLkr(string $amount): ?string
    {
        $amount = trim($amount);
        if ($amount === '' || ! is_numeric($amount)) {
            return null;
        }

        $number = (float) $amount;
        if ($number < 0) {
            return null;
        }

        return number_format($number, 2, '.', '').' LKR';
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

    private function startFeedWriter(): XMLWriter
    {
        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->startDocument('1.0', 'UTF-8');
        $writer->setIndent(true);

        $writer->startElement('rss');
        $writer->writeAttribute('version', '2.0');
        $writer->writeAttribute('xmlns:g', 'http://base.google.com/ns/1.0');

        $writer->startElement('channel');
        $writer->writeElement('title', (string) (config('app.name') ?: 'Printair'));
        $writer->writeElement('link', url('/'));
        $writer->writeElement('description', 'Google Merchant Center product feed');

        return $writer;
    }

    private function emptyFeedXml(): string
    {
        $writer = $this->startFeedWriter();
        $writer->endElement(); // channel
        $writer->endElement(); // rss
        $writer->endDocument();

        return $writer->outputMemory();
    }

    private function parseDateMax(mixed $value): ?CarbonImmutable
    {
        if (! $value) {
            return null;
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
