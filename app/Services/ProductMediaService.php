<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

class ProductMediaService
{
    public function storeMediaForProduct(
        Product $product,
        array $images,
        ?int $primaryIndex,
        array $imageSort,
        array $attachments,
        array $attachmentSort
    ): void {
        $userId = Auth::user()?->id;
        $disk = 'public';

        $baseDir = "products/{$product->id}";
        $imageDir = "{$baseDir}/images";
        $attachDir = "{$baseDir}/attachments";

        // 1) Images
        if (!empty($images)) {
            // Clear existing featured flags (if any)
            $product->images()->update(['is_featured' => 0]);

            foreach ($images as $i => $file) {
                if (!$file instanceof UploadedFile) {
                    continue;
                }

                $path = $file->storePublicly($imageDir, $disk);

                $product->images()->create([
                    'disk' => $disk,
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime' => $file->getMimeType(),
                    'size_bytes' => $file->getSize(),
                    'alt_text' => null,
                    'sort_index' => (int) ($imageSort[$i] ?? $i),
                    'is_featured' => ($primaryIndex !== null && (int) $primaryIndex === (int) $i) ? 1 : 0,
                    'meta' => [
                        'ext' => $file->getClientOriginalExtension(),
                    ],
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }

            // Ensure at least one featured image
            if ($product->images()->where('is_featured', true)->count() === 0) {
                $first = $product->images()->orderBy('sort_index')->first();
                if ($first instanceof ProductImage) {
                    $first->update(['is_featured' => 1]);
                }
            }
        }

        // 2) Attachments (nullable)
        if (!empty($attachments)) {
            foreach ($attachments as $j => $file) {
                if (!$file instanceof UploadedFile) {
                    continue;
                }

                $path = $file->storePublicly($attachDir, $disk);

                $product->attachments()->create([
                    'label' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => 'other',
                    'visibility' => 'internal',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }
        }
    }
}
