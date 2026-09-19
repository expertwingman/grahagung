<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Lead;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index()
    {
        $query = Activity::with('createdBy', 'subject');
        $this->scopeByRole($query);

        $activities = $query->orderBy('scheduled_at', 'desc')->paginate(20);

        return view('activities.index', compact('activities'));
    }

    public function create()
    {
        // Hanya tampilkan lead & project yang boleh dilihat user
        $leads    = Lead::visibleTo(auth()->user())->orderBy('name')->get();
        $projects = Project::visibleTo(auth()->user())->orderBy('name')->get();
        return view('activities.create', compact('leads', 'projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'         => 'required|in:call,meeting,email,whatsapp,follow_up,note,status_change',
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'subject_type' => 'required|in:lead,project',
            'subject_id'   => 'required|integer',
            'status'       => 'required|in:planned,done,cancelled',
            'scheduled_at' => 'nullable|date',
        ]);

        // Pastikan lead/proyek yang dipilih memang boleh diakses user ini,
        // bukan sekadar "ada di database".
        $subjectClass = $validated['subject_type'] === 'lead'
            ? Lead::class
            : Project::class;

        $boleh = $subjectClass::visibleTo(auth()->user())
            ->whereKey($validated['subject_id'])
            ->exists();

        abort_unless($boleh, 403, 'Data yang dipilih bukan milik Anda.');

        $validated['subject_type'] = $subjectClass;

        $validated['created_by'] = auth()->id();

        if ($validated['status'] === 'done') {
            $validated['completed_at'] = now();
        }

        Activity::create($validated);

        return redirect()->route('activities.index')
            ->with('success', 'Aktivitas berhasil ditambahkan!');
    }

    public function destroy(Activity $activity)
    {
        abort_unless($this->canAccess($activity), 403);

        $activity->delete();

        return redirect()->route('activities.index')
            ->with('success', 'Aktivitas berhasil dihapus!');
    }

    public function markDone(Activity $activity)
    {
        abort_unless($this->canAccess($activity), 403);

        $activity->update([
            'status'       => 'done',
            'completed_at' => now(),
        ]);

        return redirect()->route('activities.index')
            ->with('success', 'Aktivitas ditandai selesai!');
    }

    /**
     * Filter query aktivitas berdasarkan role (via created_by).
     */
    private function scopeByRole(Builder $query): void
    {
        $user = auth()->user();

        if ($user->isDirektur()) {
            return; // semua data
        }

        if ($user->isManajer()) {
            $ids   = $user->staffMembers()->pluck('id')->toArray();
            $ids[] = $user->id;
            $query->whereIn('created_by', $ids);
            return;
        }

        $query->where('created_by', $user->id);
    }

    /**
     * Cek apakah user boleh mengakses aktivitas ini.
     */
    private function canAccess(Activity $activity): bool
    {
        $user = auth()->user();

        if ($user->isDirektur()) {
            return true;
        }

        if ($user->isManajer()) {
            $ids   = $user->staffMembers()->pluck('id')->toArray();
            $ids[] = $user->id;
            return in_array($activity->created_by, $ids);
        }

        return $activity->created_by === $user->id;
    }
}
