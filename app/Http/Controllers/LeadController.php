<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Product;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Lead::class);

        $user  = auth()->user();
        $query = Lead::with('assignedTo', 'product');

        if ($user->isStaff()) {
            $query->where('assigned_to', $user->id);
        } elseif ($user->isManajer()) {
            $staffIds   = $user->staffMembers()->pluck('id')->toArray();
            $staffIds[] = $user->id;
            $query->whereIn('assigned_to', $staffIds);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'ilike', '%' . $request->search . '%')
                  ->orWhere('company', 'ilike', '%' . $request->search . '%')
                  ->orWhere('phone', 'ilike', '%' . $request->search . '%')
                  ->orWhere('email', 'ilike', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter cepat: hanya lead yang perlu di-follow up
        if ($request->boolean('needs_followup')) {
            $query->needsFollowUp();
        }

        $leads    = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        // Daftar sumber diambil dinamis dari data yang ada (sesuai role),
        // supaya filter selalu cocok dengan sumber riil di database.
        $sources = Lead::visibleTo($user)
            ->whereNotNull('source')
            ->where('source', '!=', '')
            ->distinct()
            ->orderBy('source')
            ->pluck('source');

        // Nomor WA yang bentrok (dipegang >1 sales) — untuk tandai badge di tabel
        $conflictingPhones = Lead::conflictingPhones();

        return view('leads.index', compact('leads', 'products', 'sources', 'conflictingPhones'));
    }

    public function create()
    {
        $this->authorize('create', Lead::class);

        return view('leads.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Lead::class);

        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'phone'             => 'nullable|string|max:20',
            'email'             => 'nullable|email|max:255',
            'company'           => 'nullable|string|max:255',
            'source'            => 'nullable|string|max:100',
            'status'            => 'required|in:no_respon,respon,kirim_pl,survey,utj,closing,batal',
            'value'             => 'nullable|numeric|min:0',
            'notes'             => 'nullable|string',
            'address'           => 'nullable|string|max:255',
            'city'              => 'nullable|string|max:100',
            'budget_range'      => 'nullable|string|max:50',
            'interest_type'     => 'nullable|string|max:255',
            'location_interest' => 'nullable|string|max:255',
            'follow_up_date'    => 'nullable|date',
            'survey_plan'       => 'nullable|string',
            'survey_result'     => 'nullable|string',
            'utj_status'        => 'nullable|boolean',
            'utj_date'          => 'nullable|date',
            'cancel_reason'     => 'nullable|string',
        ]);

        $validated['created_by']  = auth()->id();
        $validated['assigned_to'] = auth()->id();
        $validated['utj_status']  = $request->has('utj_status');
        $validated['phone']       = self::formatPhone($request->input('phone'), $request->input('phone_code', '62'));
        $validated['wa_phone']    = $validated['phone'];

        Lead::create($validated);

        return redirect()->route('leads.index')->with('success', 'Lead berhasil ditambahkan!');
    }

    public function show(Lead $lead)
    {
        $this->authorize('view', $lead);

        return view('leads.show', compact('lead'));
    }

    public function edit(Lead $lead)
    {
        $this->authorize('update', $lead);

        return view('leads.edit', compact('lead'));
    }

    public function update(Request $request, Lead $lead)
    {
        $this->authorize('update', $lead);

        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'phone'             => 'nullable|string|max:20',
            'email'             => 'nullable|email|max:255',
            'company'           => 'nullable|string|max:255',
            'source'            => 'nullable|string|max:100',
            'status'            => 'required|in:no_respon,respon,kirim_pl,survey,utj,closing,batal',
            'value'             => 'nullable|numeric|min:0',
            'notes'             => 'nullable|string',
            'address'           => 'nullable|string|max:255',
            'city'              => 'nullable|string|max:100',
            'budget_range'      => 'nullable|string|max:50',
            'interest_type'     => 'nullable|string|max:255',
            'location_interest' => 'nullable|string|max:255',
            'follow_up_date'    => 'nullable|date',
            'survey_plan'       => 'nullable|string',
            'survey_result'     => 'nullable|string',
            'utj_status'        => 'nullable|boolean',
            'utj_date'          => 'nullable|date',
            'cancel_reason'     => 'nullable|string',
        ]);

        $validated['utj_status'] = $request->has('utj_status');

        // Hanya update phone jika diisi, dan konsisten pakai phone_code
        if (!empty($validated['phone'])) {
            $formatted             = self::formatPhone($validated['phone'], $request->input('phone_code', '62'));
            $validated['phone']    = $formatted;
            $validated['wa_phone'] = $formatted;
        }

        $lead->update($validated);

        return redirect()->route('leads.index')->with('success', 'Lead berhasil diupdate!');
    }

    public function destroy(Lead $lead)
    {
        $this->authorize('delete', $lead);

        $lead->delete(); // soft delete — data masuk arsip, bisa dipulihkan

        return redirect()->route('leads.index')
            ->with('success', "Lead \"{$lead->name}\" dipindahkan ke arsip. Bisa dipulihkan oleh manajer/direktur.");
    }

    /**
     * Pulihkan lead yang sudah diarsip.
     * Hanya manajer & direktur yang boleh.
     */
    public function restore(int $id)
    {
        $user = auth()->user();
        abort_unless($user->isManajer() || $user->isDirektur(), 403);

        $lead = Lead::onlyTrashed()->findOrFail($id);
        $lead->restore();

        return redirect()->route('leads.archived')
            ->with('success', "Lead \"{$lead->name}\" berhasil dipulihkan.");
    }

    /**
     * Hapus permanen lead dari arsip.
     * Hanya direktur yang boleh.
     */
    public function forceDelete(int $id)
    {
        $user = auth()->user();
        abort_unless($user->isDirektur(), 403);

        $lead = Lead::onlyTrashed()->findOrFail($id);
        $name = $lead->name;
        $lead->forceDelete();

        return redirect()->route('leads.archived')
            ->with('success', "Lead \"{$name}\" dihapus permanen.");
    }

    /**
     * Halaman arsip — lead yang sudah dihapus.
     * Hanya manajer & direktur.
     */
    public function archived()
    {
        $user = auth()->user();
        abort_unless($user->isManajer() || $user->isDirektur(), 403);

        $leads = Lead::onlyTrashed()
            ->with('assignedTo', 'product')
            ->when($user->isManajer(), function ($q) use ($user) {
                $ids   = $user->staffMembers()->pluck('id')->toArray();
                $ids[] = $user->id;
                $q->whereIn('assigned_to', $ids);
            })
            ->orderBy('deleted_at', 'desc')
            ->paginate(20);

        return view('leads.archived', compact('leads'));
    }

    /**
     * Tandai lead sudah di-follow up hari ini (set follow_up_date = hari ini).
     * Tombol satu-klik untuk sales — lead langsung keluar dari daftar "perlu FU".
     */
    public function markFollowedUp(Lead $lead)
    {
        // Pakai aturan akses yang sama dengan edit (staff hanya lead miliknya)
        $this->authorize('update', $lead);

        $lead->update(['follow_up_date' => now()->toDateString()]);

        return back()->with('success', "Lead \"{$lead->name}\" ditandai sudah di-follow up hari ini.");
    }

    /**
     * Helper: normalisasi nomor telepon ke format internasional.
     * Contoh: (0812-3456, 62) → 6281234560
     */
    private static function formatPhone(?string $phone, string $countryCode = '62'): ?string
    {
        if (empty($phone)) {
            return null;
        }

        $phone = preg_replace('/\D/', '', $phone);
        $code  = preg_replace('/\D/', '', $countryCode);

        // Hapus leading 0
        if (str_starts_with($phone, '0')) {
            $phone = substr($phone, 1);
        }

        // Hapus kode negara di depan jika sudah ada
        if (str_starts_with($phone, $code)) {
            $phone = substr($phone, strlen($code));
        }

        return $code . $phone;
    }
}
