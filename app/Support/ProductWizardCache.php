<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class ProductWizardCache
{
    public static function key(Product $product, int $userId): string
    {
        return "wizard:product:{$product->id}:user:{$userId}";
    }

    public static function rememberLookups(string $key, int $seconds, \Closure $callback)
    {
        return Cache::remember($key, $seconds, $callback);
    }

    public static function putState(Product $product, int $userId, array $state, int $seconds = 3600): void
    {
        Cache::put(self::key($product, $userId), $state, $seconds);
    }

    public static function getState(Product $product, int $userId): array
    {
        return Cache::get(self::key($product, $userId), []);
    }

    public static function forgetState(Product $product, int $userId): void
    {
        Cache::forget(self::key($product, $userId));
    }
}

