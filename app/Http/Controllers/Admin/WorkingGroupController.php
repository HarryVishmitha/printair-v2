<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWorkingGroupRequest;
use App\Http\Requests\UpdateWorkingGroupRequest;
use App\Models\WorkingGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ActivityLogger; // adjust namespace if different
use Symfony\Component\HttpFoundation\Response;

class WorkingGroupController extends Controller
{
    

    /**
     * List working groups (for Blade index view).
     */
    public function index(Request $request)
    {
        $search = $request->string('search')->toString();

        $groups = WorkingGroup::query()
            ->search($search)
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.working-groups.index', [
            'workingGroups' => $groups,
            'search'        => $search,
        ]);
    }

    /**
     * Show create form.
     */
    public function create()
    {
        return view('admin.working-groups.create');
    }

    /**
     * Store new working group.
     */
    public function store(StoreWorkingGroupRequest $request)
    {
        $user = $request->user();
        $data = $this->normalizeFlags($request->validated());

        $group = DB::transaction(function () use ($data) {
            return WorkingGroup::create($data);
        });

        ActivityLogger::log(
            $user,
            'working-groups.create',
            'Created working group',
            [
                'working_group_id' => $group->id,
                'name'             => $group->name,
                'slug'             => $group->slug,
                'description'      => $group->description,
                'is_shareable'     => $group->is_shareable,
                'is_restricted'    => $group->is_restricted,
                'is_staff_group'   => $group->is_staff_group,
            ]
        );

        return redirect()
            ->route('admin.working-groups.index')
            ->with('success', 'Working group created successfully.');
    }

    /**
     * Show edit form.
     */
    public function edit(WorkingGroup $workingGroup)
    {
        return view('admin.working-groups.edit', [
            'workingGroup' => $workingGroup,
        ]);
    }

    /**
     * Update existing working group.
     */
    public function update(UpdateWorkingGroupRequest $request, WorkingGroup $workingGroup)
    {
        $user = $request->user();
        $before = $workingGroup->replicate(); // snapshot for log

        $data = $this->normalizeFlags($request->validated());

        // Protect the public group slug.
        if ($workingGroup->slug === WorkingGroup::PUBLIC_SLUG) {
            unset($data['slug']);
        }

        DB::transaction(function () use ($workingGroup, $data) {
            $workingGroup->update($data);
        });

        $changes = $workingGroup->getChanges();

        ActivityLogger::log(
            $user,
            'working-groups.update',
            'Updated working group',
            [
                'working_group_id' => $workingGroup->id,
                'before' => [
                    'name'          => $before->name,
                    'slug'          => $before->slug,
                    'description'   => $before->description,
                    'is_shareable'  => $before->is_shareable,
                    'is_restricted' => $before->is_restricted,
                    'is_staff_group'=> $before->is_staff_group,
                ],
                'after' => [
                    'name'          => $workingGroup->name,
                    'slug'          => $workingGroup->slug,
                    'description'   => $workingGroup->description,
                    'is_shareable'  => $workingGroup->is_shareable,
                    'is_restricted' => $workingGroup->is_restricted,
                    'is_staff_group'=> $workingGroup->is_staff_group,
                ],
                'changed_fields' => array_keys($changes),
            ]
        );

        return redirect()
            ->route('admin.working-groups.index')
            ->with('success', 'Working group updated successfully.');
    }

    /**
     * Delete working group.
     */
    public function destroy(Request $request, WorkingGroup $workingGroup)
    {
        $user = $request->user();

        if ($workingGroup->slug === WorkingGroup::PUBLIC_SLUG) {
            return back()->with('error', 'The public working group cannot be deleted.');
        }

        // Optional: prevent delete if it is still in use
        if ($workingGroup->users()->exists()) {
            return back()->with('error', 'This working group is assigned to users and cannot be deleted.');
        }

        $snapshot = $workingGroup->toArray();

        DB::transaction(function () use ($workingGroup) {
            $workingGroup->delete();
        });

        ActivityLogger::log(
            $user,
            'working-groups.delete',
            'Deleted working group',
            $snapshot
        );

        return redirect()
            ->route('admin.working-groups.index')
            ->with('success', 'Working group deleted successfully.');
    }

    /**
     * Normalize checkbox/boolean flags and enforce rules.
     */
    protected function normalizeFlags(array $data): array
    {
        // Convert to booleans (for Blade checkboxes).
        $data['is_shareable']   = !empty($data['is_shareable']);
        $data['is_restricted']  = !empty($data['is_restricted']);
        $data['is_staff_group'] = !empty($data['is_staff_group']);

        // Business rule: restricted groups should not be shareable.
        if ($data['is_restricted']) {
            $data['is_shareable'] = false;
        }

        return $data;
    }
}
