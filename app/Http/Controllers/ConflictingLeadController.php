<?php

namespace App\Http\Controllers;

use App\Models\Lead;

class ConflictingLeadController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Hanya manajer & direktur yang boleh akses
        abort_unless($user->isManajer() || $user->isDirektur(), 403);

        // Ambil semua lead yang bentrok, dikelompokkan per nomor WA.
        // Direktur lihat semua; manajer hanya yang melibatkan timnya.
        $query = Lead::conflicting()->with('assignedTo', 'product');

        if ($user->isManajer()) {
            $staffIds   = $user->staffMembers()->pluck('id')->toArray();
            $staffIds[] = $user->id;
            // Tampilkan grup bentrok yang setidaknya satu lead-nya milik tim manajer ini
            $relevantPhones = Lead::conflicting()
                ->whereIn('assigned_to', $staffIds)
                ->pluck('wa_phone')
                ->unique()
                ->toArray();
            $query->whereIn('wa_phone', $relevantPhones);
        }

        $groups = $query->orderBy('wa_phone')->get()->groupBy('wa_phone');

        return view('leads.conflicting', compact('groups'));
    }
}
