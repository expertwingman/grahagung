<?php

namespace App\Http\Controllers;

use App\Models\Pipeline;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PipelineController extends Controller
{
    public function index()
    {
        $stages = [
            'new'         => 'New',
            'contacted'   => 'Contacted',
            'survey'      => 'Survey',
            'proposal'    => 'Proposal',
            'negotiation' => 'Negotiation',
            'won'         => 'Won',
            'lost'        => 'Lost',
        ];

        // Filter pipeline sesuai role: staff lihat miliknya, manajer lihat timnya, direktur semua
        $pipelines = Pipeline::visibleTo(auth()->user())
            ->with('lead', 'assignedTo')
            ->orderBy('order')
            ->get()
            ->groupBy('stage');

        return view('pipeline.index', compact('stages', 'pipelines'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lead_id'             => ['required', Rule::exists('leads', 'id')->where(
                fn ($q) => $q->whereIn('assigned_to', $this->visibleUserIds())
            )],
            'stage'               => 'required|in:new,contacted,survey,proposal,negotiation,won,lost',
            'value'               => 'nullable|numeric|min:0',
            'expected_close_date' => 'nullable|date',
            'notes'               => 'nullable|string',
        ]);

        $validated['assigned_to'] = auth()->id();
        $validated['order'] = Pipeline::where('stage', $validated['stage'])->count();

        Pipeline::create($validated);

        return redirect()->route('pipeline.index')
            ->with('success', 'Deal berhasil ditambahkan ke pipeline!');
    }

    public function updateStage(Request $request, Pipeline $pipeline)
    {
        // Pastikan user hanya bisa ubah pipeline yang boleh dilihatnya
        abort_unless($this->canAccess($pipeline), 403);

        $request->validate([
            'stage' => 'required|in:new,contacted,survey,proposal,negotiation,won,lost',
        ]);

        $pipeline->update(['stage' => $request->stage]);

        return response()->json(['success' => true]);
    }

    public function destroy(Pipeline $pipeline)
    {
        abort_unless($this->canAccess($pipeline), 403);

        $pipeline->delete();

        return redirect()->route('pipeline.index')
            ->with('success', 'Deal dihapus dari pipeline!');
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
     * Cek apakah user boleh mengakses pipeline ini sesuai role.
     */
    private function canAccess(Pipeline $pipeline): bool
    {
        $user = auth()->user();

        if ($user->isDirektur()) {
            return true;
        }

        if ($user->isManajer()) {
            $ids   = $user->staffMembers()->pluck('id')->toArray();
            $ids[] = $user->id;
            return in_array($pipeline->assigned_to, $ids);
        }

        return $pipeline->assigned_to === $user->id;
    }
}
