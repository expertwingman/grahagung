<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/** Halaman CRM: daftar kunjungan lapangan dengan foto dan lokasi. */
class KunjunganController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Visit::with(['user:id,name', 'lead:id,name,status,assigned_to', 'photos'])
            ->orderByDesc('visited_at');

        if ($user->isStaff()) {
            $query->where('user_id', $user->id);
        } elseif ($user->isManajer()) {
            $ids   = $user->staffMembers()->pluck('id')->toArray();
            $ids[] = $user->id;
            $query->whereIn('user_id', $ids);
        }

        if ($request->filled('sales')) $query->where('user_id', (int) $request->sales);
        if ($request->filled('dari'))  $query->whereDate('visited_at', '>=', $request->dari);
        if ($request->filled('sampai')) $query->whereDate('visited_at', '<=', $request->sampai);
        if ($request->filled('minat')) $query->where('interest_level', $request->minat);

        $visits = $query->paginate(20)->withQueryString();

        foreach ($visits as $v) {
            $v->foto_urls = $v->photos->map(function ($p) {
                try { return Storage::disk('supabase')->temporaryUrl($p->photo_path, now()->addHour()); }
                catch (\Throwable) { return null; }
            })->filter()->values();
        }

        $salesOptions = $user->isStaff() ? collect() :
            ($user->isDirektur()
                ? \App\Models\User::where('is_active', true)->orderBy('name')->get(['id', 'name'])
                : $user->staffMembers()->where('is_active', true)->orderBy('name')->get(['id', 'name']));

        $hariIni = (clone $query)->whereDate('visited_at', today())->count();
        $mingguIni = (clone $query)->where('visited_at', '>=', now()->startOfWeek())->count();

        return view('kunjungan.index', compact('visits', 'salesOptions', 'hariIni', 'mingguIni'));
    }
}
