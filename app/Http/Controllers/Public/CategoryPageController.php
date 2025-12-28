<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryPageController extends Controller
{
    public function show(Request $request, Category $category)
    {
        abort_unless($category->is_active, 404);

        $category->loadMissing(['parent']);

        $parent = $category->parent && $category->parent->is_active ? $category->parent : null;

        $children = Category::query()
            ->where('parent_id', $category->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'parent_id',
                'name',
                'slug',
                'cover_image_path',
                'short_description',
                'seo_description',
                'seo_keywords',
            ]);

        $breadcrumbs = [
            ['label' => 'Home', 'href' => route('home')],
        ];
        if ($parent) {
            $breadcrumbs[] = [
                'label' => $parent->name,
                'href' => route('categories.show', ['category' => $parent->slug]),
            ];
        }
        $breadcrumbs[] = ['label' => $category->name, 'href' => null];

        $coverUrl = $this->imageUrl($category->cover_image_path) ?? asset('assets/placeholders/product-placeholder.svg');

        $seoTitle = $category->seo_title ?: $category->name;
        $seoDescription = $category->seo_description ?: $category->short_description ?: $category->description;
        $seoDescription = is_string($seoDescription) ? trim($seoDescription) : null;
        if ($seoDescription === '') {
            $seoDescription = null;
        }

        $keywords = $this->keywords($category->seo_keywords);

        $products = Product::query()
            ->active()
            ->visibleToPublic()
            ->where('category_id', $category->id)
            ->where('product_type', '!=', 'finishing')
            ->with([
                'images:id,product_id,path,is_featured,sort_index',
                'seo:product_id,seo_description',
                'category:id,name,slug',
            ])
            ->select([
                'id',
                'category_id',
                'name',
                'slug',
                'product_type',
                'min_qty',
                'short_description',
                'allow_custom_size',
                'allow_predefined_sizes',
                'requires_dimensions',
                'status',
            ])
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $seo = [
            'title' => $seoTitle.' · Printair',
            'description' => $seoDescription,
            'keywords' => $category->seo_keywords,
            'canonical' => route('categories.show', ['category' => $category->slug]),
            'image' => $coverUrl,
        ];

        return view('categories.show', [
            'seo' => $seo,
            'category' => $category,
            'parent' => $parent,
            'children' => $children,
            'products' => $products,
            'breadcrumbs' => $breadcrumbs,
            'keywords' => $keywords,
            'coverUrl' => $coverUrl,
        ]);
    }

    private function keywords(?string $raw): array
    {
        $raw = is_string($raw) ? trim($raw) : '';
        if ($raw === '') {
            return [];
        }

        $parts = preg_split('/[,#\n]+/', $raw) ?: [];
        $out = [];
        foreach ($parts as $p) {
            $p = trim((string) $p);
            if ($p === '') {
                continue;
            }
            $p = preg_replace('/\s+/', ' ', $p);
            $out[] = $p;
        }

        return array_values(array_unique($out));
    }

    private function imageUrl(?string $path): ?string
    {
        $path = is_string($path) ? trim($path) : '';
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
