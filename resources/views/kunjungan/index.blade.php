<x-app-layout title="Kunjungan Lapangan">

@php
    $minatMeta = [
        'panas'  => 'bg-red-100 text-red-700',
        'hangat' => 'bg-amber-100 text-amber-700',
        'dingin' => 'bg-slate-100 text-slate-600',
    ];
    $datangLabel = ['sendiri' => 'sendiri', 'pasangan' => 'dengan pasangan', 'keluarga' => 'dengan keluarga', 'teman' => 'dengan teman'];
@endphp

<div class="flex flex-wrap items-end justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Kunjungan Lapangan</h1>
        <p class="text-sm text-gray-500">Setiap calon pembeli yang datang ke lokasi, tercatat dari aplikasi sales.</p>
    </div>
    <div class="flex gap-3">
        <div class="rounded-lg bg-white px-4 py-2 shadow-sm">
            <div class="text-xs text-gray-500">Hari ini</div>
            <div class="text-xl font-bold text-gray-800">{{ $hariIni }}</div>
        </div>
        <div class="rounded-lg bg-white px-4 py-2 shadow-sm">
            <div class="text-xs text-gray-500">Minggu ini</div>
            <div class="text-xl font-bold text-gray-800">{{ $mingguIni }}</div>
        </div>
    </div>
</div>

<form method="GET" class="mt-4 flex flex-wrap gap-2 rounded-lg bg-white p-3 shadow-sm">
    @if($salesOptions->isNotEmpty())
    <select name="sales" class="rounded-lg border-gray-300 text-sm">
        <option value="">Semua sales</option>
        @foreach($salesOptions as $s)
            <option value="{{ $s->id }}" @selected(request('sales') == $s->id)>{{ $s->name }}</option>
        @endforeach
    </select>
    @endif
    <select name="minat" class="rounded-lg border-gray-300 text-sm">
        <option value="">Semua minat</option>
        <option value="panas"  @selected(request('minat')==='panas')>Panas</option>
        <option value="hangat" @selected(request('minat')==='hangat')>Hangat</option>
        <option value="dingin" @selected(request('minat')==='dingin')>Dingin</option>
    </select>
    <input type="date" name="dari"   value="{{ request('dari') }}"   class="rounded-lg border-gray-300 text-sm">
    <input type="date" name="sampai" value="{{ request('sampai') }}" class="rounded-lg border-gray-300 text-sm">
    <button class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-medium text-white">Filter</button>
    <a href="{{ route('kunjungan.index') }}" class="rounded-lg px-3 py-2 text-sm text-gray-500">Reset</a>
</form>

<div class="mt-4 space-y-3">
    @forelse($visits as $v)
        <div class="rounded-lg bg-white p-4 shadow-sm">
            <div class="flex flex-wrap gap-4">
                {{-- foto --}}
                <div class="flex shrink-0 gap-2">
                    @forelse($v->foto_urls as $url)
                        <a href="{{ $url }}" target="_blank" rel="noopener" class="block h-24 w-24 overflow-hidden rounded-lg bg-gray-100">
                            <img src="{{ $url }}" alt="" class="h-full w-full object-cover">
                        </a>
                    @empty
                        <div class="flex h-24 w-24 items-center justify-center rounded-lg bg-gray-100 text-xs text-gray-400">tanpa foto</div>
                    @endforelse
                </div>

                {{-- isi --}}
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ $v->lead ? route('leads.show', $v->lead) : '#' }}" class="font-semibold text-gray-800 hover:underline">
                            {{ $v->client_name }}
                        </a>
                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold uppercase {{ $minatMeta[$v->interest_level] ?? '' }}">
                            {{ $v->interest_level }}
                        </span>
                        @if($v->came_with)
                            <span class="text-xs text-gray-500">{{ $datangLabel[$v->came_with] ?? $v->came_with }}</span>
                        @endif
                    </div>

                    <div class="mt-1 text-sm text-gray-600">
                        {{ \Illuminate\Support\Str::title(str_replace('-', ' ', $v->project_slug)) }}
                        @if($v->unit_type_slug) · tipe {{ \Illuminate\Support\Str::title(str_replace('-', ' ', $v->unit_type_slug)) }} @endif
                        @if($v->unit_block) · blok {{ $v->unit_block }} @endif
                    </div>

                    <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500">
                        <span>👤 {{ $v->user?->name }}</span>
                        <span>🕒 {{ $v->visited_at?->translatedFormat('d M Y H:i') }}</span>
                        @if($v->server_captured_at && abs($v->server_captured_at->diffInMinutes($v->visited_at)) > 30)
                            <span class="text-amber-600" title="Waktu HP dan server berbeda jauh — kemungkinan dikirim setelah offline">⚠ dikirim {{ $v->server_captured_at->diffForHumans($v->visited_at, true) }} kemudian</span>
                        @endif
                        @if($v->latitude && $v->longitude)
                            <a href="https://maps.google.com/?q={{ $v->latitude }},{{ $v->longitude }}" target="_blank" rel="noopener" class="text-blue-600 hover:underline">📍 Lihat lokasi</a>
                        @else
                            <span class="text-gray-400">📍 tanpa GPS</span>
                        @endif
                        @if($v->lead && $v->lead->assigned_to !== $v->user_id)
                            <span class="text-red-600">⚠ lead milik sales lain</span>
                        @endif
                    </div>

                    @if($v->next_action)
                        <div class="mt-2 inline-block rounded bg-blue-50 px-2 py-1 text-xs text-blue-800">
                            Tindak lanjut: {{ $v->next_action }}
                            @if($v->next_action_date) · {{ $v->next_action_date->translatedFormat('d M') }} @endif
                        </div>
                    @endif

                    @if($v->notes)
                        <p class="mt-2 text-sm text-gray-600">{{ $v->notes }}</p>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="rounded-lg bg-white p-10 text-center text-sm text-gray-500 shadow-sm">
            Belum ada kunjungan tercatat.
        </div>
    @endforelse
</div>

<div class="mt-4">{{ $visits->links() }}</div>

</x-app-layout>
