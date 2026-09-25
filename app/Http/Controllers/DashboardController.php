<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Lead;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;

/**
 * Dashboard per peran.
 *
 * Sales    → antrean tugas hari ini, corong pribadi
 * Manajer  → gerak tim, chart kompetitif sumber, corong tim, cold lead
 * Direktur → semua tim + nilai pipeline
 */
class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();

        if ($user->isStaff()) return $this->sales($user);
        if ($user->isManajer()) return $this->manajer($user);
        return $this->direktur($user);
    }

    /* ================================================================== */
    /*  SALES — "Apa yang harus saya kerjakan sekarang?"                   */
    /* ================================================================== */

    private function sales(User $user)
    {
        $leads = Lead::where('assigned_to', $user->id);

        // 5 lead paling mendesak: baru ditugaskan (belum disentuh) + cold terlama
        $tugasHariIni = (clone $leads)
            ->whereNotIn('status', ['closing', 'batal'])
            ->orderByRaw("CASE WHEN follow_up_date IS NULL THEN 0 ELSE 1 END")
            ->orderBy('follow_up_date')
            ->orderBy('created_at')
            ->take(5)->with('product')->get();

        // Corong pribadi
        $corong = $this->hitungCorong(clone $leads);

        // Kunjungan hari ini
        $kunjunganHariIni = Visit::where('user_id', $user->id)
            ->whereDate('visited_at', today())
            ->with(['lead:id,name,status', 'photos'])->latest('visited_at')->get();

        // Progres minggu ini
        $mingguIni = Activity::where('created_by', $user->id)
            ->where('created_at', '>=', now()->startOfWeek())->count();
        $kunjunganMingguIni = Visit::where('user_id', $user->id)
            ->where('visited_at', '>=', now()->startOfWeek())->count();

        // Lead baru yang belum disentuh
        $belumDisentuh = (clone $leads)
            ->whereNull('follow_up_date')
            ->whereNotIn('status', ['closing', 'batal'])
            ->count();

        return view('dashboard.sales', compact(
            'tugasHariIni', 'corong', 'kunjunganHariIni',
            'mingguIni', 'kunjunganMingguIni', 'belumDisentuh'
        ));
    }

    /* ================================================================== */
    /*  MANAJER — "Tim saya bergerak atau macet?"                          */
    /* ================================================================== */

    private function manajer(User $user)
    {
        $staffIds   = $user->staffMembers()->pluck('id')->toArray();
        $staffIds[] = $user->id;
        $staff      = User::whereIn('id', $staffIds)->where('is_active', true)->orderBy('name')->get();

        $leads = Lead::whereIn('assigned_to', $staffIds);

        // Corong tim
        $corong = $this->hitungCorong(clone $leads);

        // Chart kompetitif sumber lead (per minggu, 8 minggu terakhir)
        $sumberPerMinggu = $this->sumberPerMinggu(clone $leads);

        // Kontak per sales minggu ini
        $kontakPerSales = Activity::whereIn('created_by', $staffIds)
            ->where('created_at', '>=', now()->startOfWeek())
            ->selectRaw('created_by, COUNT(*) as total')
            ->groupBy('created_by')->pluck('total', 'created_by');
        $kunjunganPerSales = Visit::whereIn('user_id', $staffIds)
            ->where('visited_at', '>=', now()->startOfWeek())
            ->selectRaw('user_id, COUNT(*) as total')
            ->groupBy('user_id')->pluck('total', 'user_id');

        // Lead website belum ditugaskan
        $webLeads = Lead::where('source', 'website')
            ->whereNull('assigned_to')->latest()->take(5)->get();
        $webLeadsCount = Lead::where('source', 'website')
            ->whereNull('assigned_to')->count();

        // Cold lead per sales
        $coldPerSales = [];
        foreach ($staff as $s) {
            $c = Lead::where('assigned_to', $s->id)->cold(30)->count();
            if ($c > 0) $coldPerSales[] = ['name' => $s->name, 'count' => $c, 'id' => $s->id];
        }
        usort($coldPerSales, fn ($a, $b) => $b['count'] <=> $a['count']);

        // Daftar sales untuk penugasan
        $salesOptions = $staff->where('role', 'staff');

        // Kunjungan terbaru tim
        $kunjunganTerbaru = Visit::whereIn('user_id', $staffIds)
            ->with(['user:id,name', 'lead:id,name,status', 'photos'])
            ->latest('visited_at')->take(5)->get();

        return view('dashboard.manajer', compact(
            'staff', 'corong', 'sumberPerMinggu',
            'kontakPerSales', 'kunjunganPerSales',
            'webLeads', 'webLeadsCount', 'salesOptions',
            'coldPerSales', 'kunjunganTerbaru'
        ));
    }

    /* ================================================================== */
    /*  DIREKTUR — "Berapa nilai pipeline dan siapa yang menghasilkan?"    */
    /* ================================================================== */

    private function direktur(User $user)
    {
        $leads = Lead::query();
        $allStaff = User::where('is_active', true)->orderBy('name')->get();

        // Corong seluruh perusahaan
        $corong = $this->hitungCorong(clone $leads);

        // Chart kompetitif sumber
        $sumberPerMinggu = $this->sumberPerMinggu(clone $leads);

        // Kontak per sales
        $kontakPerSales = Activity::where('created_at', '>=', now()->startOfWeek())
            ->selectRaw('created_by, COUNT(*) as total')
            ->groupBy('created_by')->pluck('total', 'created_by');
        $kunjunganPerSales = Visit::where('visited_at', '>=', now()->startOfWeek())
            ->selectRaw('user_id, COUNT(*) as total')
            ->groupBy('user_id')->pluck('total', 'user_id');

        // Pipeline
        $pipelineCount = \App\Models\Pipeline::whereNotIn('stage', ['won', 'lost'])->count();
        $pipelineValue = \App\Models\Pipeline::whereNotIn('stage', ['won', 'lost'])->sum('value');
        $wonCount      = \App\Models\Pipeline::where('stage', 'won')->count();
        $wonValue      = \App\Models\Pipeline::where('stage', 'won')->sum('value');

        // Lead website belum ditugaskan
        $webLeads = Lead::where('source', 'website')
            ->whereNull('assigned_to')->latest()->take(5)->get();
        $webLeadsCount = Lead::where('source', 'website')
            ->whereNull('assigned_to')->count();

        // Cold lead per sales
        $coldPerSales = [];
        foreach ($allStaff->where('role', 'staff') as $s) {
            $c = Lead::where('assigned_to', $s->id)->cold(30)->count();
            if ($c > 0) $coldPerSales[] = ['name' => $s->name, 'count' => $c, 'id' => $s->id];
        }
        usort($coldPerSales, fn ($a, $b) => $b['count'] <=> $a['count']);

        $salesOptions = $allStaff->where('role', 'staff');

        // Kunjungan terbaru
        $kunjunganTerbaru = Visit::with(['user:id,name', 'lead:id,name,status', 'photos'])
            ->latest('visited_at')->take(5)->get();

        return view('dashboard.direktur', compact(
            'allStaff', 'corong', 'sumberPerMinggu',
            'kontakPerSales', 'kunjunganPerSales',
            'pipelineCount', 'pipelineValue', 'wonCount', 'wonValue',
            'webLeads', 'webLeadsCount', 'salesOptions',
            'coldPerSales', 'kunjunganTerbaru'
        ));
    }

    /* ================================================================== */
    /*  SHARED HELPERS                                                      */
    /* ================================================================== */

    /** Corong: berapa lead di tiap status. */
    private function hitungCorong($query): array
    {
        $raw = (clone $query)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')->pluck('total', 'status');

        $urutan = ['no_respon', 'respon', 'kirim_pl', 'survey', 'utj', 'closing'];
        $hasil = [];
        foreach ($urutan as $s) {
            $hasil[] = [
                'status' => $s,
                'label'  => Lead::STATUSES[$s] ?? $s,
                'total'  => $raw[$s] ?? 0,
            ];
        }
        return $hasil;
    }

    /** Sumber lead per minggu, 8 minggu terakhir — untuk chart kompetitif. */
    private function sumberPerMinggu($query): array
    {
        $rows = (clone $query)
            ->selectRaw("source, DATE_TRUNC('week', created_at)::date as minggu, COUNT(*) as total")
            ->where('created_at', '>=', now()->subWeeks(8)->startOfWeek())
            ->groupByRaw("source, DATE_TRUNC('week', created_at)::date")
            ->orderBy('minggu')
            ->get();

        // Daftar minggu
        $mingguList = [];
        $awal = now()->subWeeks(7)->startOfWeek();
        for ($i = 0; $i < 8; $i++) {
            $mingguList[] = $awal->copy()->addWeeks($i)->format('Y-m-d');
        }

        // Kelompokkan per sumber
        $sumberSet = $rows->pluck('source')->unique()->filter()->values();
        $series = [];
        foreach ($sumberSet as $src) {
            $data = [];
            foreach ($mingguList as $m) {
                $data[] = $rows->where('source', $src)->where('minggu', $m)->first()?->total ?? 0;
            }
            $series[] = ['name' => $this->labelSumber($src), 'key' => $src, 'data' => $data];
        }
        // Urutkan: sumber dengan total terbanyak di atas
        usort($series, fn ($a, $b) => array_sum($b['data']) <=> array_sum($a['data']));

        // Label minggu: "14 Sep", "21 Sep", ...
        $labels = array_map(fn ($d) => \Carbon\Carbon::parse($d)->format('d M'), $mingguList);

        return ['labels' => $labels, 'series' => $series];
    }

    /** Label sumber yang manusiawi (bukan nama_database). */
    private function labelSumber(?string $src): string
    {
        return match ($src) {
            'iklan_meta'  => 'Meta Ads',
            'hp_cs'       => 'Telepon CS',
            'website'     => 'Website',
            'google'      => 'Google',
            'referral'    => 'Referral',
            'tiktok'      => 'TikTok',
            'spanduk'     => 'Spanduk',
            'lewat'       => 'Lewat lokasi',
            'pameran'     => 'Pameran',
            'survey'      => 'Kunjungan langsung',
            null, ''      => 'Lainnya',
            default       => ucfirst(str_replace('_', ' ', $src)),
        };
    }
}
