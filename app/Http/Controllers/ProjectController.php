<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    public function index()
    {
        // Filter proyek sesuai role
        $projects = Project::visibleTo(auth()->user())
            ->with('lead', 'assignedTo')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        $leads = Lead::visibleTo(auth()->user())->orderBy('name')->get();
        return view('projects.create', compact('leads'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'lead_id'     => ['nullable', Rule::exists('leads', 'id')->where(
                fn ($q) => $q->whereIn('assigned_to', $this->visibleUserIds())
            )],
            'status'      => 'required|in:planning,in_progress,on_hold,completed,cancelled',
            'priority'    => 'required|in:low,medium,high',
            'value'       => 'nullable|numeric|min:0',
            'progress'    => 'nullable|numeric|min:0|max:100',
            'start_date'  => 'nullable|date',
            'due_date'    => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        $validated['created_by']  = auth()->id();
        $validated['assigned_to'] = auth()->id();

        Project::create($validated);

        return redirect()->route('projects.index')
            ->with('success', 'Proyek berhasil ditambahkan!');
    }

    public function show(Project $project)
    {
        abort_unless($this->canAccess($project), 403);

        return view('projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        abort_unless($this->canAccess($project), 403);

        $leads = Lead::visibleTo(auth()->user())->orderBy('name')->get();
        return view('projects.edit', compact('project', 'leads'));
    }

    public function update(Request $request, Project $project)
    {
        abort_unless($this->canAccess($project), 403);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'lead_id'     => ['nullable', Rule::exists('leads', 'id')->where(
                fn ($q) => $q->whereIn('assigned_to', $this->visibleUserIds())
            )],
            'status'      => 'required|in:planning,in_progress,on_hold,completed,cancelled',
            'priority'    => 'required|in:low,medium,high',
            'value'       => 'nullable|numeric|min:0',
            'progress'    => 'nullable|numeric|min:0|max:100',
            'start_date'  => 'nullable|date',
            'due_date'    => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        if ($validated['status'] === 'completed' && !$project->completed_date) {
            $validated['completed_date'] = now();
        }

        $project->update($validated);

        return redirect()->route('projects.index')
            ->with('success', 'Proyek berhasil diupdate!');
    }

    public function destroy(Project $project)
    {
        abort_unless($this->canAccess($project), 403);

        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Proyek berhasil dihapus!');
    }

    /**
     * ID user yang datanya boleh disentuh oleh user yang login.
     */
    private function visibleUserIds(): array
    {
        $user = auth()->user();

        if ($user->isDirektur()) {
            return \App\Models\User::pluck('id')->toArray();
        }

        if ($user->isManajer()) {
            $ids   = $user->staffMembers()->pluck('id')->toArray();
            $ids[] = $user->id;
            return $ids;
        }

        return [$user->id];
    }

    /**
     * Cek apakah user boleh mengakses proyek ini sesuai role.
     */
    private function canAccess(Project $project): bool
    {
        $user = auth()->user();

        if ($user->isDirektur()) {
            return true;
        }

        if ($user->isManajer()) {
            $ids   = $user->staffMembers()->pluck('id')->toArray();
            $ids[] = $user->id;
            return in_array($project->assigned_to, $ids);
        }

        return $project->assigned_to === $user->id;
    }
}
