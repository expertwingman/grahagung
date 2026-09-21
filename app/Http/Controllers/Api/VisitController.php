<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Lead;
use App\Models\Visit;
use App\Models\VisitPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * API aplikasi sales lapangan.
 *
 * Satu kunjungan = satu request POST /api/visits (multipart, foto ikut).
 * Server yang menentukan waktu; HP hanya mengirim koordinat.
 */
class VisitController extends Controller
{
    private const STATUS_SEBELUM_SURVEY = ['no_respon', 'respon', 'kirim_pl'];

    /* ------------------------------------------------------------------ */
    /*  Data pendukung form                                                 */
    /* ------------------------------------------------------------------ */

    /** Proyek, tipe, dan unit tersedia — dari schema web (sumber yang sama dengan website). */
    public function catalog()
    {
        $projects = DB::table('web.projects')->select('id', 'slug', 'name', 'city')->orderBy('name')->get();
        $types    = DB::table('web.unit_types')->select('id', 'project_id', 'slug', 'name', 'lb', 'lt', 'floors')->orderBy('lb')->get();
        $units    = DB::table('web.units')
            ->select('project_id', 'unit_type_id', 'block', 'status')
            ->whereIn('status', ['available', 'reserved', 'booked'])
            ->orderBy('block')->get();

        $keluar = $projects->map(function ($p) use ($types, $units) {
            return [
                'slug'  => $p->slug,
                'name'  => $p->name,
                'city'  => $p->city,
                'types' => $types->where('project_id', $p->id)->values()->map(fn ($t) => [
                    'slug' => $t->slug, 'name' => $t->name,
                    'lb' => (int) $t->lb, 'lt' => $t->lt ? (int) $t->lt : null, 'floors' => (int) $t->floors,
                ]),
                'units' => $units->where('project_id', $p->id)->pluck('block')->values(),
            ];
        });

        return response()->json([
            'projects' => $keluar,
            'options'  => [
                'sources'         => ['iklan_meta' => 'Iklan Facebook/Instagram', 'tiktok' => 'TikTok', 'website' => 'Website', 'google' => 'Pencarian Google', 'referral' => 'Rekomendasi teman/keluarga', 'spanduk' => 'Spanduk / papan iklan', 'lewat' => 'Kebetulan lewat lokasi', 'pameran' => 'Pameran', 'lainnya' => 'Lainnya'],
                'came_with'       => ['sendiri' => 'Sendiri', 'pasangan' => 'Pasangan', 'keluarga' => 'Keluarga', 'teman' => 'Teman / rekan'],
                'payment_method'  => ['kpr' => 'KPR', 'cash' => 'Tunai / bertahap'],
                'occupation'      => ['swasta' => 'Karyawan swasta', 'pns_bumn' => 'PNS / BUMN / TNI-Polri', 'wiraswasta' => 'Wiraswasta', 'profesional' => 'Profesional (dokter, pengacara, dll)', 'lainnya' => 'Lainnya'],
                'budget_range'    => ['<500' => '< Rp 500 jt', '500-1000' => 'Rp 500 jt – 1 M', '1000-1500' => 'Rp 1 – 1,5 M', '1500-2500' => 'Rp 1,5 – 2,5 M', '>2500' => '> Rp 2,5 M'],
                'salary_range'    => ['<5' => '< Rp 5 jt', '5-10' => 'Rp 5 – 10 jt', '10-20' => 'Rp 10 – 20 jt', '20-50' => 'Rp 20 – 50 jt', '>50' => '> Rp 50 jt'],
                'interest_level'  => ['dingin' => 'Dingin — sekadar lihat', 'hangat' => 'Hangat — mempertimbangkan', 'panas' => 'Panas — siap tanda jadi'],
                'next_action'     => ['kirim_pl' => 'Kirim price list', 'survei_ulang' => 'Survei ulang bersama keluarga', 'proses_kpr' => 'Bantu proses KPR', 'tanda_jadi' => 'Tanda jadi', 'follow_up' => 'Follow-up biasa'],
            ],
            'consent' => 'Data Bapak/Ibu kami simpan untuk keperluan penawaran properti dari Graha Agung Kencana Group.',
        ]);
    }

    /** Cari lead berdasarkan nomor HP — untuk mengisi form otomatis. */
    public function lookup(Request $request)
    {
        $request->validate(['phone' => 'required|string|max:20']);
        $wa = self::normalisasi($request->phone);
        if (! $wa) {
            return response()->json(['found' => false, 'reason' => 'nomor_tidak_valid']);
        }

        $lead = Lead::with(['product', 'assignedTo'])
            ->where(fn ($q) => $q->where('wa_phone', $wa)->orWhere('phone', $wa))
            ->orderByDesc('id')->first();

        if (! $lead) {
            return response()->json(['found' => false, 'wa_phone' => $wa]);
        }

        $user = $request->user();

        return response()->json([
            'found'   => true,
            'mine'    => $lead->assigned_to === $user->id,
            'owner'   => $lead->assignedTo?->name,
            'lead'    => [
                'id'             => $lead->id,
                'name'           => $lead->name,
                'wa_phone'       => $lead->wa_phone,
                'city'           => $lead->city,
                'source'         => $lead->source,
                'status'         => $lead->status,
                'status_label'   => $lead->statusLabel(),
                'product'        => $lead->product?->name,
                'occupation'     => $lead->occupation,
                'budget_range'   => $lead->budget_range,
                'salary_range'   => $lead->salary_range,
                'payment_method' => $lead->payment_method,
                'first_contact'  => optional($lead->input_date ?? $lead->created_at)->toDateString(),
                'last_contact'   => optional($lead->follow_up_date ?? $lead->last_contacted_at)->toDateString(),
                'visits'         => Visit::where('lead_id', $lead->id)->count(),
            ],
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  Kunjungan                                                           */
    /* ------------------------------------------------------------------ */

    public function index(Request $request)
    {
        $visits = Visit::with(['photos', 'lead:id,name,status'])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('visited_at')
            ->paginate(20);

        $visits->getCollection()->transform(fn ($v) => $this->keluaran($v));

        return response()->json($visits);
    }

    /**
     * Simpan kunjungan. Multipart: field + photos[] (maks 3, 6 MB/foto).
     * Idempoten lewat client_uuid — aman dikirim ulang saat sinkron offline.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'client_uuid'      => 'nullable|string|max:40',
            // siapa
            'phone'            => 'required|string|max:20',
            'name'             => 'required|string|max:120',
            'city'             => 'nullable|string|max:80',
            'source'           => 'nullable|string|max:30',
            'came_with'        => 'nullable|in:sendiri,pasangan,keluarga,teman',
            // profil
            'occupation'       => 'nullable|string|max:60',
            'budget_range'     => 'nullable|string|max:20',
            'salary_range'     => 'nullable|string|max:20',
            'payment_method'   => 'nullable|in:kpr,cash',
            // kunjungan
            'project_slug'     => 'required|string|max:60',
            'unit_type_slug'   => 'nullable|string|max:60',
            'unit_block'       => 'nullable|string|max:30',
            'interest_level'   => 'required|in:dingin,hangat,panas',
            'next_action'      => 'nullable|string|max:120',
            'next_action_date' => 'nullable|date',
            'notes'            => 'nullable|string|max:2000',
            'latitude'         => 'nullable|numeric|between:-90,90',
            'longitude'        => 'nullable|numeric|between:-180,180',
            'visited_at'       => 'nullable|date',
            'photos'           => 'nullable|array|max:3',
            'photos.*'         => 'image|max:6144',
        ]);

        // Idempoten: kalau uuid sudah pernah masuk, kembalikan yang ada
        if (! empty($data['client_uuid'])) {
            $ada = Visit::with(['photos', 'lead'])->where('client_uuid', $data['client_uuid'])->first();
            if ($ada) {
                return response()->json(['message' => 'Sudah tersimpan sebelumnya.', 'visit' => $this->keluaran($ada), 'duplicate' => true]);
            }
        }

        $user = $request->user();
        $wa   = self::normalisasi($data['phone']);
        if (! $wa) {
            return response()->json(['message' => 'Nomor HP tidak valid.'], 422);
        }

        $projectName = DB::table('web.projects')->where('slug', $data['project_slug'])->value('name') ?? $data['project_slug'];

        $hasil = DB::transaction(function () use ($data, $user, $wa, $projectName, $request) {

            /* ---- 1. Lead: cari, buat, atau perbarui ---- */
            $lead = Lead::where(fn ($q) => $q->where('wa_phone', $wa)->orWhere('phone', $wa))
                ->orderByDesc('id')->first();
            $conflict = false;

            if (! $lead) {
                $lead = Lead::create([
                    'name'        => $data['name'],
                    'phone'       => $wa,
                    'wa_phone'    => $wa,
                    'source'      => $data['source'] ?? 'survey',
                    'status'      => 'survey',
                    'assigned_to' => $user->id,
                    'created_by'  => $user->id,
                    'input_date'  => now()->toDateString(),
                    'notes'       => "Dibuat dari kunjungan lapangan ke {$projectName}.",
                ]);
            } elseif ($lead->assigned_to && $lead->assigned_to !== $user->id) {
                // Lead milik sales lain — tetap dicatat, tapi ditandai bentrok
                $conflict = true;
            } elseif (! $lead->assigned_to) {
                $lead->assigned_to = $user->id;
            }

            // Profil: isi hanya yang dikirim, jangan menimpa dengan kosong
            foreach (['city', 'occupation', 'budget_range', 'salary_range', 'payment_method'] as $k) {
                if (! empty($data[$k])) $lead->{$k} = $data[$k];
            }
            if (! empty($data['source']) && empty($lead->source)) $lead->source = $data['source'];

            // Produk (proyek) dari tabel products CRM kalau ada yang namanya cocok
            if (! $lead->product_id) {
                $pid = DB::table('products')->where('name', $projectName)->value('id');
                if ($pid) $lead->product_id = $pid;
            }

            // Status naik ke survey; catat waktu kontak
            if (in_array($lead->status, self::STATUS_SEBELUM_SURVEY)) {
                $lead->status = 'survey';
            }
            $lead->follow_up_date    = now()->toDateString();
            $lead->last_contacted_at = now();
            if (! empty($data['notes'])) {
                $lead->survey_result = trim(($lead->survey_result ? $lead->survey_result . "\n\n" : '')
                    . now()->format('d/m/Y') . ' — ' . $data['notes']);
            }
            if (! empty($data['next_action'])) {
                $lead->survey_plan = $data['next_action']
                    . (! empty($data['next_action_date']) ? ' (' . $data['next_action_date'] . ')' : '');
            }
            $lead->save();

            /* ---- 2. Kunjungan (snapshot) ---- */
            $visit = Visit::create([
                'user_id'            => $user->id,
                'lead_id'            => $lead->id,
                'client_uuid'        => $data['client_uuid'] ?? (string) Str::uuid(),
                'client_id'          => $lead->id,
                'client_name'        => $data['name'],
                'client_phone'       => $wa,
                'project_slug'       => $data['project_slug'],
                'unit_type_slug'     => $data['unit_type_slug'] ?? null,
                'unit_block'         => $data['unit_block'] ?? null,
                'interest_level'     => $data['interest_level'],
                'came_with'          => $data['came_with'] ?? null,
                'next_action'        => $data['next_action'] ?? null,
                'next_action_date'   => $data['next_action_date'] ?? null,
                'notes'              => $data['notes'] ?? null,
                'status'             => 'visited',
                'latitude'           => $data['latitude'] ?? null,
                'longitude'          => $data['longitude'] ?? null,
                'visited_at'         => ! empty($data['visited_at']) ? $data['visited_at'] : now(),
                'server_captured_at' => now(),
                'payload'            => collect($data)->except(['photos'])->all(),
            ]);

            /* ---- 3. Foto ---- */
            foreach ($request->file('photos', []) as $file) {
                $path = "kunjungan/{$visit->id}/" . Str::uuid() . '.' . strtolower($file->extension());
                Storage::disk('supabase')->put($path, file_get_contents($file));
                VisitPhoto::create([
                    'visit_id'   => $visit->id,
                    'photo_path' => $path,
                    'photo_url'  => $path,   // URL bertanda tangan dibuat saat dibaca
                ]);
            }

            /* ---- 4. Aktivitas ---- */
            $minat = ['dingin' => 'dingin', 'hangat' => 'hangat', 'panas' => 'PANAS'][$data['interest_level']];
            $blok  = ! empty($data['unit_block']) ? " (blok {$data['unit_block']})" : '';
            Activity::create([
                'type'         => 'meeting',
                'title'        => "Kunjungan lapangan: {$projectName}{$blok} — minat {$minat}",
                'description'  => $data['notes'] ?? null,
                'subject_type' => Lead::class,
                'subject_id'   => $lead->id,
                'status'       => 'done',
                'completed_at' => now(),
                'created_by'   => $user->id,
            ]);

            return ['visit' => $visit->load(['photos', 'lead']), 'conflict' => $conflict, 'owner' => $lead->assignedTo?->name];
        });

        return response()->json([
            'message'  => $hasil['conflict']
                ? "Tersimpan. Perhatian: lead ini terdaftar atas nama {$hasil['owner']}."
                : 'Kunjungan tersimpan dan lead diperbarui.',
            'conflict' => $hasil['conflict'],
            'visit'    => $this->keluaran($hasil['visit']),
        ], 201);
    }

    public function show(Request $request, Visit $visit)
    {
        if ($visit->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }
        return response()->json($this->keluaran($visit->load(['photos', 'lead'])));
    }

    /** Tambah foto ke kunjungan yang sudah ada (untuk sinkron foto terpisah). */
    public function uploadPhoto(Request $request, Visit $visit)
    {
        if ($visit->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }
        $request->validate(['photo' => 'required|image|max:6144', 'caption' => 'nullable|string|max:255']);

        $file = $request->file('photo');
        $path = "kunjungan/{$visit->id}/" . Str::uuid() . '.' . strtolower($file->extension());
        Storage::disk('supabase')->put($path, file_get_contents($file));

        $photo = VisitPhoto::create([
            'visit_id' => $visit->id, 'photo_path' => $path, 'photo_url' => $path, 'caption' => $request->caption,
        ]);

        return response()->json(['message' => 'Foto tersimpan.', 'photo' => $this->foto($photo)], 201);
    }

    /** Sinkron batch tanpa foto — dipertahankan untuk kompatibilitas. Foto menyusul via uploadPhoto. */
    public function sync(Request $request)
    {
        $request->validate(['visits' => 'required|array|max:100']);
        $hasil = [];
        foreach ($request->input('visits') as $item) {
            $sub = Request::create('/api/visits', 'POST', $item);
            $sub->setUserResolver(fn () => $request->user());
            $res = $this->store($sub);
            $hasil[] = ['client_uuid' => $item['client_uuid'] ?? null, 'status' => $res->getStatusCode(), 'body' => $res->getData()];
        }
        return response()->json(['results' => $hasil]);
    }

    /* ------------------------------------------------------------------ */
    /*  Helper                                                              */
    /* ------------------------------------------------------------------ */

    private function keluaran(Visit $v): array
    {
        return [
            'id'                 => $v->id,
            'client_uuid'        => $v->client_uuid,
            'lead'               => $v->lead ? ['id' => $v->lead->id, 'name' => $v->lead->name, 'status' => $v->lead->status] : null,
            'client_name'        => $v->client_name,
            'client_phone'       => $v->client_phone,
            'project_slug'       => $v->project_slug,
            'unit_type_slug'     => $v->unit_type_slug,
            'unit_block'         => $v->unit_block,
            'interest_level'     => $v->interest_level,
            'came_with'          => $v->came_with,
            'next_action'        => $v->next_action,
            'next_action_date'   => optional($v->next_action_date)->toDateString(),
            'notes'              => $v->notes,
            'latitude'           => $v->latitude,
            'longitude'          => $v->longitude,
            'visited_at'         => optional($v->visited_at)->toIso8601String(),
            'server_captured_at' => optional($v->server_captured_at)->toIso8601String(),
            'photos'             => $v->photos->map(fn ($p) => $this->foto($p))->values(),
        ];
    }

    private function foto(VisitPhoto $p): array
    {
        try {
            $url = Storage::disk('supabase')->temporaryUrl($p->photo_path, now()->addHours(2));
        } catch (\Throwable) {
            $url = $p->photo_url;
        }
        return ['id' => $p->id, 'url' => $url, 'caption' => $p->caption];
    }

    /** 08xx / +62xx / 8xx → 62xx */
    public static function normalisasi(string $input): ?string
    {
        $angka = preg_replace('/\D/', '', $input);
        if ($angka === '') return null;
        if (str_starts_with($angka, '0')) $angka = '62' . substr($angka, 1);
        elseif (str_starts_with($angka, '8')) $angka = '62' . $angka;
        return preg_match('/^62\d{9,13}$/', $angka) ? $angka : null;
    }
}
