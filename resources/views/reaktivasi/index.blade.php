<x-app-layout title="Reaktivasi Cold Lead">

@php
    $total   = $leads->count();
    $target  = max($total, 1);
    $progres = $total ? min(100, round($contactedToday / $target * 100)) : 0;
    $statusMeta = [
        'survey'    => ['label' => 'Sudah survei',    'warna' => 'bg-emerald-500/15 text-emerald-300 ring-emerald-500/30', 'hangat' => 'PALING HANGAT'],
        'respon'    => ['label' => 'Pernah merespon', 'warna' => 'bg-sky-500/15 text-sky-300 ring-sky-500/30',             'hangat' => 'HANGAT'],
        'kirim_pl'  => ['label' => 'Dapat price list','warna' => 'bg-amber-500/15 text-amber-300 ring-amber-500/30',       'hangat' => 'SEDANG'],
        'no_respon' => ['label' => 'Belum merespon',  'warna' => 'bg-slate-500/15 text-slate-300 ring-slate-500/30',       'hangat' => 'DINGIN'],
    ];
@endphp

<div class="-m-6 min-h-full bg-[#0c0f14] p-6 text-slate-200" style="font-family: 'Plus Jakarta Sans', Figtree, ui-sans-serif, system-ui, sans-serif;">

    {{-- ================= HERO ================= --}}
    <div class="rounded-2xl border border-white/10 bg-gradient-to-br from-[#131720] to-[#0a1a2e] p-6 lg:p-8">
        <div class="flex flex-wrap items-start justify-between gap-6">
            <div class="max-w-2xl">
                <div class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.25em] text-[#3ecf8e]">
                    <span class="inline-block h-2 w-2 rounded-full bg-[#3ecf8e] shadow-[0_0_12px_#3ecf8e]"></span>
                    Mesin Lead · Reaktivasi
                </div>
                <h1 class="mt-3 text-3xl font-bold leading-tight text-white lg:text-4xl">
                    {{ $total }} lead sudah dibayar iklannya — dan diam {{ $days }}+ hari.
                </h1>
                <p class="mt-3 text-sm leading-6 text-slate-400">
                    Setiap lead di daftar ini pernah tertarik. Menghubungi ulang tidak butuh biaya iklan.
                    Pesan sudah disiapkan per lead — tinggal buka WhatsApp, kirim, catat hasilnya.
                </p>
            </div>

            <div class="grid grid-cols-3 gap-3 text-center">
                <div class="rounded-xl bg-white/5 px-4 py-3 ring-1 ring-white/10">
                    <div class="text-2xl font-bold text-white">{{ $total }}</div>
                    <div class="mt-0.5 text-[11px] uppercase tracking-wider text-slate-400">Cold lead</div>
                </div>
                <div class="rounded-xl bg-white/5 px-4 py-3 ring-1 ring-white/10">
                    <div class="text-2xl font-bold text-white">{{ $contactedToday }}</div>
                    <div class="mt-0.5 text-[11px] uppercase tracking-wider text-slate-400">Dikontak hari ini</div>
                </div>
                <div class="rounded-xl bg-white/5 px-4 py-3 ring-1 ring-white/10">
                    <div class="text-2xl font-bold text-white">{{ $oldest }}</div>
                    <div class="mt-0.5 text-[11px] uppercase tracking-wider text-slate-400">Hari terlama</div>
                </div>
            </div>
        </div>

        {{-- Progres --}}
        <div class="mt-6">
            <div class="flex items-center justify-between text-xs text-slate-400">
                <span>Progres hari ini</span>
                <span class="font-mono">{{ $contactedToday }} / {{ $total }} · {{ $progres }}%</span>
            </div>
            <div class="mt-2 h-2 overflow-hidden rounded-full bg-white/10">
                <div class="h-full rounded-full bg-gradient-to-r from-[#3ecf8e] to-[#7fffd4] transition-all" style="width: {{ $progres }}%"></div>
            </div>
        </div>

        {{-- Filter ambang --}}
        <div class="mt-6 flex flex-wrap items-center gap-2 text-xs">
            <span class="text-slate-400">Diam minimal:</span>
            @foreach([14, 30, 60, 90] as $d)
                <a href="{{ route('reaktivasi.index', ['days' => $d]) }}"
                   class="rounded-full px-3 py-1.5 font-semibold ring-1 transition
                          {{ $days === $d ? 'bg-[#3ecf8e] text-[#0c0f14] ring-[#3ecf8e]' : 'bg-white/5 text-slate-300 ring-white/10 hover:bg-white/10' }}">
                    {{ $d }} hari
                </a>
            @endforeach
        </div>
    </div>

    {{-- ================= RINGKASAN ================= --}}
    @if($total > 0)
    <div class="mt-6 grid gap-4 lg:grid-cols-[1fr_1fr]">
        <div class="rounded-2xl border border-white/10 bg-[#131720] p-5">
            <div class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400">Menurut kehangatan</div>
            <div class="mt-4 space-y-2.5">
                @foreach($statusMeta as $st => $m)
                    @php $n = $byStatus[$st] ?? 0; @endphp
                    @if($n > 0)
                    <div class="flex items-center gap-3">
                        <span class="w-36 shrink-0 text-xs text-slate-300">{{ $m['label'] }}</span>
                        <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-white/5">
                            <div class="h-full rounded-full bg-[#5b8dee]" style="width: {{ round($n / $total * 100) }}%"></div>
                        </div>
                        <span class="w-8 text-right font-mono text-xs text-white">{{ $n }}</span>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-white/10 bg-[#131720] p-5">
            <div class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400">Menurut sumber</div>
            <div class="mt-4 space-y-2.5">
                @foreach($bySource->take(5) as $src => $n)
                    <div class="flex items-center gap-3">
                        <span class="w-36 shrink-0 truncate text-xs text-slate-300">{{ $src }}</span>
                        <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-white/5">
                            <div class="h-full rounded-full bg-[#f5a623]" style="width: {{ round($n / $total * 100) }}%"></div>
                        </div>
                        <span class="w-8 text-right font-mono text-xs text-white">{{ $n }}</span>
                    </div>
                @endforeach
            </div>
            <p class="mt-4 text-[11px] leading-5 text-slate-500">
                Sumber dengan cold lead terbanyak = anggaran iklan yang paling banyak "menguap" tanpa follow-up.
            </p>
        </div>
    </div>
    @endif

    {{-- ================= DAFTAR ================= --}}
    <div class="mt-6">
        <div class="flex items-end justify-between">
            <div>
                <div class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400">Antrean prioritas</div>
                <h2 class="mt-1 text-xl font-bold text-white">Mulai dari yang paling hangat</h2>
            </div>
            <div class="text-xs text-slate-500">Diurutkan: kehangatan, lalu terlama diam</div>
        </div>

        @if($total === 0)
            <div class="mt-6 rounded-2xl border border-dashed border-white/15 bg-[#131720] p-10 text-center">
                <div class="text-3xl">🎉</div>
                <p class="mt-3 font-semibold text-white">Tidak ada lead yang diam {{ $days }}+ hari.</p>
                <p class="mt-1 text-sm text-slate-400">Coba ambang yang lebih pendek, atau semua lead memang sudah tersentuh.</p>
            </div>
        @endif

        <div class="mt-4 space-y-3" id="antrean">
            @foreach($leads as $lead)
                @php $m = $statusMeta[$lead->status] ?? $statusMeta['no_respon']; @endphp
                <article class="rounded-2xl border border-white/10 bg-[#131720] p-5 transition hover:border-white/20"
                         data-lead="{{ $lead->id }}">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        {{-- kiri: identitas --}}
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-base font-bold text-white">{{ $lead->name }}</h3>
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider ring-1 {{ $m['warna'] }}">
                                    {{ $m['hangat'] }}
                                </span>
                            </div>

                            <div class="mt-1.5 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-400">
                                <span class="font-mono">{{ $lead->wa_phone ?? $lead->phone ?? '—' }}</span>
                                <span>{{ $lead->product?->name ?? 'Tanpa proyek' }}</span>
                                <span>{{ $m['label'] }}</span>
                                <span class="text-[#f26c6c]">diam {{ $lead->daysSilent() }} hari</span>
                                @if(!auth()->user()->isStaff())
                                    <span>· {{ $lead->assignedTo?->name ?? 'belum ditugaskan' }}</span>
                                @endif
                            </div>

                            @if($lead->notes)
                                <p class="mt-2 line-clamp-2 text-xs leading-5 text-slate-500">{{ $lead->notes }}</p>
                            @endif
                        </div>

                        {{-- kanan: aksi --}}
                        <div class="flex shrink-0 flex-wrap items-center gap-2">
                            @if($lead->wa_url)
                                <a href="{{ $lead->wa_url }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-2 rounded-xl bg-[#25D366] px-4 py-2.5 text-sm font-bold text-[#0c0f14] transition hover:brightness-110"
                                   onclick="tandaiDibuka({{ $lead->id }})">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5.1-1.3A10 10 0 1 0 12 2Zm4.5 12.1c-.2-.1-1.5-.7-1.7-.8s-.4-.1-.6.1-.6.8-.8 1c-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.2-.4.7-1.3.1-.2 0-.3 0-.5l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.2.3-.9.9-.9 2.2s.9 2.5 1.1 2.7c.1.2 1.9 2.9 4.6 4 .6.3 1.1.4 1.5.6.6.2 1.2.2 1.6.1.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.1-1.2l-.5-.3Z"/></svg>
                                    Buka WhatsApp
                                </a>
                            @else
                                <span class="rounded-xl bg-white/5 px-4 py-2.5 text-xs text-slate-500 ring-1 ring-white/10">Tanpa nomor WA</span>
                            @endif

                            <button type="button" onclick="toggleDetail({{ $lead->id }})"
                                    class="rounded-xl bg-white/5 px-3 py-2.5 text-sm text-slate-300 ring-1 ring-white/10 hover:bg-white/10">
                                Pesan & hasil
                            </button>

                            <a href="{{ route('leads.show', $lead) }}" class="rounded-xl px-3 py-2.5 text-sm text-slate-400 hover:text-white">Lihat</a>
                        </div>
                    </div>

                    {{-- detail: pesan + catat hasil --}}
                    <div id="detail-{{ $lead->id }}" class="mt-4 hidden border-t border-white/10 pt-4">
                        <div class="grid gap-4 lg:grid-cols-[1.4fr_1fr]">
                            <div>
                                <div class="mb-2 flex items-center justify-between text-[11px] uppercase tracking-wider text-slate-400">
                                    <span>Pesan yang disiapkan</span>
                                    <button type="button" onclick="salin({{ $lead->id }})" class="text-[#3ecf8e] hover:underline">Salin</button>
                                </div>
                                <textarea id="pesan-{{ $lead->id }}" rows="6"
                                          class="w-full rounded-xl border-white/10 bg-[#0c0f14] p-3 text-sm leading-6 text-slate-200 focus:border-[#3ecf8e] focus:ring-0">{{ $lead->pesan }}</textarea>
                                <p class="mt-1.5 text-[11px] text-slate-500">Bisa diedit sebelum dikirim. Tombol WhatsApp memakai pesan awal.</p>
                            </div>

                            <form method="POST" action="{{ route('reaktivasi.contacted', $lead) }}" class="space-y-3">
                                @csrf
                                <div class="text-[11px] uppercase tracking-wider text-slate-400">Catat hasil</div>
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    @foreach([
                                        'terkirim'       => ['Pesan terkirim',  'bg-white/5 ring-white/10'],
                                        'tertarik'       => ['Tertarik — lanjut','bg-emerald-500/10 ring-emerald-500/30 text-emerald-300'],
                                        'tidak_aktif'    => ['Nomor tidak aktif','bg-white/5 ring-white/10'],
                                        'tidak_tertarik' => ['Tidak tertarik',   'bg-red-500/10 ring-red-500/30 text-red-300'],
                                    ] as $val => [$lbl, $cls])
                                        <label class="cursor-pointer rounded-xl px-3 py-2.5 ring-1 has-[:checked]:ring-2 has-[:checked]:ring-[#3ecf8e] {{ $cls }}">
                                            <input type="radio" name="hasil" value="{{ $val }}" class="sr-only" {{ $val === 'terkirim' ? 'checked' : '' }}>
                                            {{ $lbl }}
                                        </label>
                                    @endforeach
                                </div>
                                <input type="text" name="catatan" placeholder="Catatan singkat (opsional)"
                                       class="w-full rounded-xl border-white/10 bg-[#0c0f14] px-3 py-2 text-sm text-slate-200 placeholder:text-slate-600 focus:border-[#3ecf8e] focus:ring-0">
                                <button type="submit"
                                        class="w-full rounded-xl bg-[#3ecf8e] px-4 py-2.5 text-sm font-bold text-[#0c0f14] hover:brightness-110">
                                    Simpan & lanjut ke berikutnya
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</div>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

<script>
function toggleDetail(id) {
    document.getElementById('detail-' + id)?.classList.toggle('hidden');
}
function tandaiDibuka(id) {
    // Buka panel hasil otomatis setelah WhatsApp dibuka, supaya tidak lupa dicatat
    document.getElementById('detail-' + id)?.classList.remove('hidden');
}
function salin(id) {
    const ta = document.getElementById('pesan-' + id);
    navigator.clipboard.writeText(ta.value).then(() => {
        ta.classList.add('ring-2', 'ring-[#3ecf8e]');
        setTimeout(() => ta.classList.remove('ring-2', 'ring-[#3ecf8e]'), 800);
    });
}
</script>

</x-app-layout>
