<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();

            $filters = [
                'search' => $request->input('search'),
                'active' => $request->input('active'),
                'navbar' => $request->input('navbar'),
                'featured' => $request->input('featured'),
                'parent_id' => $request->input('parent_id'),
            ];

            $parents = Category::query()
                ->where(function ($q) use ($user) {
                    $q->where('working_group_id', $user->working_group_id)
                        ->orWhereNull('working_group_id');
                })
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name']);

            $query = Category::query()
                ->where(function ($q) use ($user) {
                    $q->where('working_group_id', $user->working_group_id)
                        ->orWhereNull('working_group_id');
                });

            if ($request->filled('search')) {
                $s = trim((string) $request->input('search'));
                $query->where(function ($q) use ($s) {
                    $q->where('name', 'like', "%{$s}%")
                        ->orWhere('slug', 'like', "%{$s}%")
                        ->orWhere('code', 'like', "%{$s}%");
                });
            }

            if ($request->filled('active')) {
                $query->where('is_active', (bool) $request->input('active'));
            }

            if ($request->filled('navbar')) {
                $query->where('show_in_navbar', (bool) $request->input('navbar'));
            }

            if ($request->filled('featured')) {
                $query->where('is_featured', (bool) $request->input('featured'));
            }

            if ($request->filled('parent_id')) {
                if ($request->input('parent_id') === '__top__') {
                    $query->whereNull('parent_id');
                } else {
                    $query->where('parent_id', (int) $request->input('parent_id'));
                }
            }

            $categories = $query
                ->with(['parent:id,name'])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(20)
                ->withQueryString();

            return view('admin.categories.index', compact('categories', 'filters', 'parents'));

        } catch (\Throwable $e) {
            Log::error('Category index failed', ['user_id' => Auth::id(), 'exception' => $e]);

            return back()->with('error', 'Unable to load categories right now. Please try again.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $user = Auth::user();

            $parents = Category::query()
                ->where(function ($q) use ($user) {
                    $q->where('working_group_id', $user->working_group_id)
                        ->orWhereNull('working_group_id');
                })
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name']);

            return view('admin.categories.create', compact('parents'));

        } catch (\Throwable $e) {
            Log::error('Category create page failed', ['user_id' => Auth::id(), 'exception' => $e]);

            return back()->with('error', 'Unable to open category create page right now.');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        try {
            $user = Auth::user();

            $category = DB::transaction(function () use ($request, $user) {

                $data = $request->validated();

                // Enforce tenant scope (important)
                $data['working_group_id'] = $user->working_group_id;

                // Normalize slug (stable URLs)
                $data['slug'] = Str::slug($data['slug']);

                // Parent scope protection (avoid linking to other tenant parents)
                if (! empty($data['parent_id'])) {
                    $parentOk = Category::query()
                        ->where('id', $data['parent_id'])
                        ->where(function ($q) use ($user) {
                            $q->where('working_group_id', $user->working_group_id)
                                ->orWhereNull('working_group_id'); // allow global parents if you use that concept
                        })
                        ->exists();

                    if (! $parentOk) {
                        throw new \RuntimeException('Invalid parent category selected.');
                    }
                }

                // Optional audit fields (remove if your table doesn't have these)
                $data['created_by'] = $user->id;
                $data['updated_by'] = $user->id;

                return Category::create($data);
            });

            ActivityLogger::log(
                $user,
                'category.created',
                "Created category: {$category->name}",
                [
                    'category_id' => $category->id,
                    'category_name' => $category->name,
                    'slug' => $category->slug,
                    'parent_id' => $category->parent_id,
                ]
            );

            return redirect()
                ->route('admin.categories.index')
                ->with('success', "Category '{$category->name}' created successfully.");

        } catch (\Throwable $e) {

            Log::error('Category store failed', [
                'user_id' => Auth::id(),
                'payload' => $request->except(['_token']),
                'exception' => $e,
            ]);

            // If the exception is our custom parent error, show that message
            $message = $e instanceof \RuntimeException
                ? $e->getMessage()
                : 'Something went wrong while creating the category. Please try again.';

            return back()
                ->withInput()
                ->with('error', $message);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        try {
            $user = Auth::user();

            // Tenant / scope protection (simple, production-safe)
            if (! is_null($category->working_group_id) && $category->working_group_id !== $user->working_group_id) {
                abort(403);
            }

            // Parent list (exclude current category)
            $parents = Category::query()
                ->where(function ($q) use ($user) {
                    $q->where('working_group_id', $user->working_group_id)
                        ->orWhereNull('working_group_id');
                })
                ->whereNull('parent_id')
                ->where('id', '!=', $category->id)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name']);

            return view('admin.categories.edit', compact('category', 'parents'));

        } catch (\Throwable $e) {
            Log::error('Category edit page failed', [
                'user_id' => Auth::id(),
                'category_id' => $category->id ?? null,
                'exception' => $e,
            ]);

            return redirect()
                ->route('admin.categories.index')
                ->with('error', 'Unable to open category edit page right now.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        try {
            $user = Auth::user();

            if (! is_null($category->working_group_id) && $category->working_group_id !== $user->working_group_id) {
                abort(403);
            }

            DB::transaction(function () use ($request, $category, $user) {
                $data = $request->validated();

                // Keep it in the same tenant scope (don’t let form change ownership)
                $data['working_group_id'] = $category->working_group_id ?? $user->working_group_id;

                // Normalize slug
                $data['slug'] = Str::slug($data['slug']);

                // Parent scope protection + block self-parent (request already blocks self)
                if (! empty($data['parent_id'])) {
                    $parentOk = Category::query()
                        ->where('id', $data['parent_id'])
                        ->where('id', '!=', $category->id)
                        ->where(function ($q) use ($user) {
                            $q->where('working_group_id', $user->working_group_id)
                                ->orWhereNull('working_group_id');
                        })
                        ->exists();

                    if (! $parentOk) {
                        throw new \RuntimeException('Invalid parent category selected.');
                    }
                }

                // Optional audit field (remove if not in DB)
                $data['updated_by'] = $user->id;

                $category->update($data);
            });

            ActivityLogger::log(
                $user,
                'category.updated',
                "Updated category: {$category->name}",
                [
                    'category_id' => $category->id,
                    'category_name' => $category->name,
                    'slug' => $category->slug,
                    'changes' => $category->getChanges(),
                ]
            );

            return redirect()
                ->route('admin.categories.index')
                ->with('success', "Category '{$category->name}' updated successfully.");

        } catch (\Throwable $e) {

            Log::error('Category update failed', [
                'user_id' => Auth::id(),
                'category_id' => $category->id ?? null,
                'payload' => $request->except(['_token', '_method']),
                'exception' => $e,
            ]);

            $message = $e instanceof \RuntimeException
                ? $e->getMessage()
                : 'Something went wrong while updating the category. Please try again.';

            return back()
                ->withInput()
                ->with('error', $message);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        try {
            $user = Auth::user();

            // Tenant / scope protection
            if (! is_null($category->working_group_id) && $category->working_group_id !== $user->working_group_id) {
                abort(403);
            }

            DB::transaction(function () use ($category) {

                /**
                 * SAFETY CHECK 1:
                 * Prevent deleting categories that have children
                 * (forces admin to reassign or deactivate instead)
                 */
                if ($category->children()->exists()) {
                    throw new \RuntimeException(
                        'This category has sub-categories. Please remove or reassign them before deleting.'
                    );
                }

                /**
                 * SAFETY CHECK 2 (future-proof):
                 * If later you add products relation, this will protect you
                 * Uncomment once products relation exists
                 */
                /*
                if ($category->products()->exists()) {
                    throw new \RuntimeException(
                        'This category is linked to products. Please reassign or deactivate instead of deleting.'
                    );
                }
                */

                /**
                 * Soft delete (recommended)
                 * Keeps data recoverable and audit-safe
                 */
                $category->delete();
            });

            ActivityLogger::log(
                $user,
                'category.deleted',
                "Deleted category: {$category->name}",
                [
                    'category_id' => $category->id,
                    'category_name' => $category->name,
                    'slug' => $category->slug,
                ]
            );

            return redirect()
                ->route('admin.categories.index')
                ->with('success', "Category '{$category->name}' deleted successfully.");

        } catch (\Throwable $e) {

            Log::error('Category delete failed', [
                'user_id' => Auth::id(),
                'category_id' => $category->id ?? null,
                'exception' => $e,
            ]);

            $message = $e instanceof \RuntimeException
                ? $e->getMessage()
                : 'Unable to delete the category at the moment. Please try again.';

            return back()->with('error', $message);
        }
    }
}
