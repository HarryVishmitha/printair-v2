<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Roll;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;


class RollController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        try {
            $this->authorize('viewAny', Roll::class);

            $q = trim((string) $request->get('q', ''));
            $material = trim((string) $request->get('material_type', ''));
            $status = $request->get('status'); // active|inactive|null

            $rolls = Roll::query()
                ->when($q !== '', function ($query) use ($q) {
                    $query->where(function ($qq) use ($q) {
                        $qq->where('name', 'like', "%{$q}%")
                           ->orWhere('slug', 'like', "%{$q}%");
                    });
                })
                ->when($material !== '', fn ($query) => $query->where('material_type', $material))
                ->when($status, function ($query) use ($status) {
                    $query->where('is_active', $status === 'active');
                })
                ->orderBy('material_type')
                ->orderBy('width_in')
                ->orderBy('name')
                ->paginate(20)
                ->withQueryString();

            $materialTypes = Roll::query()
                ->select('material_type')
                ->whereNotNull('material_type')
                ->groupBy('material_type')
                ->orderBy('material_type')
                ->pluck('material_type');

            return view('admin.rolls.index', [
                'rolls' => $rolls,
                'materialTypes' => $materialTypes,
                'filters' => [
                    'q' => $q,
                    'material_type' => $material,
                    'status' => $status,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Admin RollController@index error', [
                'user_id' => Auth::user()?->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Unable to load rolls. Please try again.');
        }
    }

    public function create()
    {
        try {
            $this->authorize('create', Roll::class);

            return view('admin.rolls.create', [
                'defaults' => [
                    'is_active' => true,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Admin RollController@create error', [
                'user_id' => Auth::user()?->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('admin.rolls.index')
                ->with('error', 'Unable to open roll creation page.');
        }
    }

    public function store(Request $request)
    {
        try {
            $this->authorize('create', Roll::class);

            $data = $request->validate([
                'name' => ['required', 'string', 'max:160'],
                'slug' => ['nullable', 'string', 'max:200'],
                'material_type' => ['required', 'string', 'max:60'],
                'width_in' => ['required', 'numeric', 'min:0.01'],
                'is_active' => ['nullable', 'boolean'],
                'meta' => ['nullable', 'array'],
            ]);

            $slug = Str::slug($data['slug'] ?: $data['name']);
            // Ensure uniqueness (simple loop, safe for admin usage)
            $base = $slug;
            $i = 2;
            while (Roll::where('slug', $slug)->exists()) {
                $slug = $base.'-'.$i;
                $i++;
            }

            Roll::create([
                'name' => $data['name'],
                'slug' => $slug,
                'material_type' => $data['material_type'],
                'width_in' => $data['width_in'],
                'is_active' => (bool) ($data['is_active'] ?? true),
                'meta' => $data['meta'] ?? null,
                'created_by' => Auth::user()?->id,
                'updated_by' => Auth::user()?->id,
            ]);

            return redirect()->route('admin.rolls.index')
                ->with('success', 'Roll created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Admin RollController@store error', [
                'user_id' => Auth::user()?->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Failed to create roll. Please try again.');
        }
    }

    public function edit(Roll $roll)
    {
        try {
            $this->authorize('update', $roll);

            return view('admin.rolls.edit', [
                'roll' => $roll,
            ]);
        } catch (\Throwable $e) {
            Log::error('Admin RollController@edit error', [
                'user_id' => Auth::user()?->id,
                'roll_id' => $roll->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('admin.rolls.index')
                ->with('error', 'Unable to open roll editor.');
        }
    }

    public function update(Request $request, Roll $roll)
    {
        try {
            $this->authorize('update', $roll);

            $data = $request->validate([
                'name' => ['required', 'string', 'max:160'],
                'slug' => ['nullable', 'string', 'max:200'],
                'material_type' => ['required', 'string', 'max:60'],
                'width_in' => ['required', 'numeric', 'min:0.01'],
                'is_active' => ['nullable', 'boolean'],
                'meta' => ['nullable', 'array'],
            ]);

            $slug = Str::slug($data['slug'] ?: $data['name']);
            if ($slug !== $roll->slug) {
                $base = $slug;
                $i = 2;
                while (Roll::where('slug', $slug)->where('id', '!=', $roll->id)->exists()) {
                    $slug = $base.'-'.$i;
                    $i++;
                }
            }

            $roll->update([
                'name' => $data['name'],
                'slug' => $slug,
                'material_type' => $data['material_type'],
                'width_in' => $data['width_in'],
                'is_active' => (bool) ($data['is_active'] ?? false),
                'meta' => $data['meta'] ?? null,
                'updated_by' => Auth::user()?->id,
            ]);

            return redirect()->route('admin.rolls.index')
                ->with('success', 'Roll updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Admin RollController@update error', [
                'user_id' => Auth::user()?->id,
                'roll_id' => $roll->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Failed to update roll. Please try again.');
        }
    }

    public function destroy(Roll $roll)
    {
        try {
            $this->authorize('delete', $roll);

            $roll->delete();

            return redirect()->route('admin.rolls.index')
                ->with('success', 'Roll deleted successfully.');
        } catch (\Throwable $e) {
            Log::warning('Admin RollController@destroy blocked', [
                'user_id' => Auth::user()?->id,
                'roll_id' => $roll->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('admin.rolls.index')
                ->with('error', 'This roll cannot be deleted (it may be in use).');
        }
    }
}

