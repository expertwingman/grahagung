<?php

namespace App\Http\Controllers;

use App\Models\ContentItem;
use App\Models\ContentPublication;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Papan konten — ide, draf, jadwal, publikasi.
 * Semua peran bisa melihat; staff bisa mengusulkan ide; manajer/direktur menyetujui.
 */
class ContentController extends Controller
{
    public function index(Request $request)
    {
        $contents = ContentItem::with(['product', 'owner'])
            ->when($request->q, fn ($q) => $q->where(fn ($w) =>
                $w->where('title', 'ilike', "%{$request->q}%")
                  ->orWhere('content_id', 'ilike', "%{$request->q}%")
            ))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->platform, fn ($q) => $q->where('platform', $request->platform))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        // Ringkasan per status untuk bar atas
        $perStatus = ContentItem::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')->pluck('total', 'status');

        // Kalender: konten yang dijadwalkan bulan ini dan depan
        $kalender = ContentItem::whereNotNull('scheduled_at')
            ->where('scheduled_at', '>=', now()->startOfMonth())
            ->where('scheduled_at', '<=', now()->addMonth()->endOfMonth())
            ->orderBy('scheduled_at')
            ->get(['id', 'title', 'platform', 'status', 'scheduled_at']);

        return view('konten.index', compact('contents', 'perStatus', 'kalender'));
    }

    public function create()
    {
        return view('konten.form', [
            'content'  => new ContentItem(),
            'products' => Product::where('is_active', true)->orderBy('name')->get(),
            'staff'    => User::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $data['content_id'] = ContentItem::generateContentId();
        $data['created_by'] = auth()->id();
        if (empty($data['owner_id'])) $data['owner_id'] = auth()->id();

        $item = ContentItem::create($data);

        return redirect()->route('konten.edit', $item)->with('success', 'Konten berhasil dibuat.');
    }

    public function edit(ContentItem $konten)
    {
        $konten->load('publications');
        return view('konten.form', [
            'content'  => $konten,
            'products' => Product::where('is_active', true)->orderBy('name')->get(),
            'staff'    => User::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, ContentItem $konten)
    {
        $data = $request->validate($this->rules());

        // Kalau status berubah ke published, catat waktu
        if ($data['status'] === 'published' && $konten->status !== 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $konten->update($data);

        // Simpan tautan publikasi kalau diisi
        if ($request->filled('pub_platform') && $request->filled('pub_url')) {
            ContentPublication::updateOrCreate(
                ['content_item_id' => $konten->id, 'platform' => $request->pub_platform],
                ['published_url' => $request->pub_url, 'published_at' => $konten->published_at ?? now(), 'status' => 'published']
            );
        }

        return back()->with('success', 'Konten diperbarui.');
    }

    public function destroy(ContentItem $konten)
    {
        $this->authorize('delete', $konten);
        $konten->delete();
        return redirect()->route('konten.index')->with('success', 'Konten dihapus.');
    }

    public function export()
    {
        $rows = ContentItem::with('owner')->latest()->get();
        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Content ID', 'Judul', 'Platform', 'Jenis', 'Status', 'Jadwal', 'Published', 'Owner']);
            foreach ($rows as $r) {
                fputcsv($out, [$r->content_id, $r->title, $r->platformLabel(), $r->typeLabel(), $r->statusLabel(),
                    $r->scheduled_at?->format('d/m/Y H:i'), $r->published_at?->format('d/m/Y H:i'), $r->owner?->name]);
            }
            fclose($out);
        };
        return response()->streamDownload($callback, 'gak-konten-' . now()->format('Ymd') . '.csv', ['Content-Type' => 'text/csv']);
    }

    private function rules(): array
    {
        return [
            'product_id'    => 'nullable|exists:products,id',
            'title'         => 'required|string|max:255',
            'idea'          => 'nullable|string',
            'caption'       => 'nullable|string',
            'platform'      => 'nullable|in:' . implode(',', array_keys(ContentItem::PLATFORMS)),
            'content_type'  => 'nullable|in:' . implode(',', array_keys(ContentItem::TYPES)),
            'status'        => 'required|in:' . implode(',', array_keys(ContentItem::STATUSES)),
            'scheduled_at'  => 'nullable|date',
            'published_at'  => 'nullable|date',
            'owner_id'      => 'nullable|exists:users,id',
            'seo_score'     => 'nullable|integer|min:0|max:100',
            'quality_score' => 'nullable|integer|min:0|max:100',
        ];
    }
}
