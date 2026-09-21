<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * REAKTIVASI COLD LEAD
 *
 * Lead yang sudah dibayar iklannya tapi diam: belum pernah di-follow-up,
 * atau follow-up terakhirnya sudah lama. Halaman ini menyusun daftar
 * prioritas, menyiapkan pesan WhatsApp yang dipersonalisasi, dan mencatat
 * setiap kontak sebagai aktivitas — sehingga progres tim terlihat.
 */
class ReaktivasiController extends Controller
{
    /** Ambang default: lead dianggap "dingin" bila diam ≥ sekian hari. */
    public const DEFAULT_DAYS = 30;

    /**
     * Urutan prioritas. Yang pernah merespon paling hangat,
     * yang tidak pernah merespon paling dingin.
     */
    private const PRIORITY = [
        'survey'    => 1,
        'respon'    => 2,
        'kirim_pl'  => 3,
        'no_respon' => 4,
    ];

    public function index(Request $request)
    {
        $user = auth()->user();
        $days = (int) $request->input('days', self::DEFAULT_DAYS);
        $days = in_array($days, [14, 30, 60, 90]) ? $days : self::DEFAULT_DAYS;

        $query = Lead::query()
            ->with(['product', 'assignedTo'])
            ->cold($days);

        // Aturan akses sama dengan daftar lead
        if ($user->isStaff()) {
            $query->where('assigned_to', $user->id);
        } elseif ($user->isManajer()) {
            $ids   = $user->staffMembers()->pluck('id')->toArray();
            $ids[] = $user->id;
            $query->whereIn('assigned_to', $ids);
        }

        $leads = $query->get()
            ->sortBy(fn ($l) => [self::PRIORITY[$l->status] ?? 9, -$l->daysSilent()])
            ->values();

        // Ringkasan
        $byStatus = $leads->groupBy('status')->map->count();
        $bySource = $leads->groupBy(fn ($l) => $l->source ?: 'lainnya')->map->count()->sortDesc();

        // Progres hari ini: berapa yang sudah dikontak lewat modul ini
        $contactedTodayQuery = Activity::where('type', 'whatsapp')
            ->where('title', 'like', 'Reaktivasi:%')
            ->whereDate('created_at', today());
        if ($user->isStaff()) {
            $contactedTodayQuery->where('created_by', $user->id);
        } elseif ($user->isManajer()) {
            $contactedTodayQuery->whereIn('created_by', $ids);
        }
        $contactedToday = $contactedTodayQuery->count();

        // Template per lead disiapkan di server supaya klien tinggal pakai
        foreach ($leads as $lead) {
            $lead->pesan = $this->template($lead, $user);
            $lead->wa_url = $lead->wa_phone
                ? 'https://wa.me/' . $lead->wa_phone . '?text=' . rawurlencode($lead->pesan)
                : null;
        }

        return view('reaktivasi.index', [
            'leads'          => $leads,
            'days'           => $days,
            'byStatus'       => $byStatus,
            'bySource'       => $bySource,
            'contactedToday' => $contactedToday,
            'oldest'         => $leads->max(fn ($l) => $l->daysSilent()) ?? 0,
        ]);
    }

    /**
     * Tandai lead sudah dikontak lewat reaktivasi:
     * catat aktivitas + perbarui follow_up_date.
     */
    public function contacted(Request $request, Lead $lead)
    {
        $this->authorize('update', $lead);

        $validated = $request->validate([
            'hasil' => 'nullable|in:terkirim,tidak_aktif,tidak_tertarik,tertarik',
            'catatan' => 'nullable|string|max:500',
        ]);

        $hasil = $validated['hasil'] ?? 'terkirim';
        $label = [
            'terkirim'       => 'Pesan terkirim',
            'tidak_aktif'    => 'Nomor tidak aktif',
            'tidak_tertarik' => 'Tidak tertarik',
            'tertarik'       => 'Tertarik — lanjut',
        ][$hasil];

        Activity::create([
            'type'         => 'whatsapp',
            'title'        => "Reaktivasi: {$label}",
            'description'  => $validated['catatan'] ?? null,
            'subject_type' => Lead::class,
            'subject_id'   => $lead->id,
            'status'       => 'done',
            'completed_at' => now(),
            'created_by'   => auth()->id(),
        ]);

        $update = ['follow_up_date' => now()->toDateString()];
        if ($hasil === 'tertarik' && $lead->status === 'no_respon') {
            $update['status'] = 'respon';
        }
        if ($hasil === 'tidak_tertarik') {
            $update['status'] = 'batal';
        }
        $lead->update($update);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'label' => $label]);
        }

        return back()->with('success', "{$lead->name}: {$label}.");
    }

    /**
     * Pesan WhatsApp yang dipersonalisasi berdasarkan status & proyek.
     * Nanti bisa diganti hasil AI; strukturnya sudah siap.
     */
    private function template(Lead $lead, $sales): string
    {
        $nama    = $this->sapaan($lead->name);
        $proyek  = $lead->product?->name ?? 'perumahan kami';
        $penjual = Str::before($sales->name, ' ');
        $t       = config('reaktivasi.templates');

        $pesan = $t[$lead->status] ?? $t['default'];

        return strtr($pesan, [
            '{nama}'   => $nama,
            '{proyek}' => $proyek,
            '{sales}'  => $penjual,
        ]);
    }

    /** "IBRAHIM MOSSADEQ" → "Pak Ibrahim"; tanpa tebakan jenis kelamin → "Bapak/Ibu Ibrahim". */
    private function sapaan(string $nama): string
    {
        $nama = trim($nama);
        $depan = Str::title(Str::before($nama, ' '));
        return $depan !== '' ? "Bapak/Ibu {$depan}" : 'Bapak/Ibu';
    }
}
