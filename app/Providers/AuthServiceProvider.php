<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Option;
use App\Models\OptionGroup;
use App\Models\Product;
use App\Models\ProductFinishingLink;
use App\Models\ProductFile;
use App\Models\ProductImage;
use App\Models\ProductPricing;
use App\Models\ProductSeo;
use App\Models\Roll;
use App\Models\Estimate;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\ProductVariantSet;
use App\Models\ProductVariantAvailabilityOverride;
use App\Policies\CategoryPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\EstimatePolicy;
use App\Policies\OrderPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\PaymentPolicy;
use App\Policies\OptionGroupPolicy;
use App\Policies\OptionPolicy;
use App\Policies\ProductPolicy;
use App\Policies\ProductFinishingLinkPolicy;
use App\Policies\ProductFilePolicy;
use App\Policies\ProductImagePolicy;
use App\Policies\ProductPricingPolicy;
use App\Policies\ProductSeoPolicy;
use App\Policies\RollPolicy;
use App\Policies\ProductVariantSetPolicy;
use App\Policies\ProductVariantAvailabilityOverridePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Product::class => ProductPolicy::class,
        Customer::class => CustomerPolicy::class,
        Category::class => CategoryPolicy::class,
        Estimate::class => EstimatePolicy::class,
        Order::class => OrderPolicy::class,
        Invoice::class => InvoicePolicy::class,
        Payment::class => PaymentPolicy::class,
        ProductPricing::class => ProductPricingPolicy::class,
        ProductFinishingLink::class => ProductFinishingLinkPolicy::class,
        ProductFile::class => ProductFilePolicy::class,
        ProductImage::class => ProductImagePolicy::class,
        ProductSeo::class => ProductSeoPolicy::class,
        Roll::class => RollPolicy::class,
        OptionGroup::class => OptionGroupPolicy::class,
        Option::class => OptionPolicy::class,
        ProductVariantSet::class => ProductVariantSetPolicy::class,
        ProductVariantAvailabilityOverride::class => ProductVariantAvailabilityOverridePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
