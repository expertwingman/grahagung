<x-app-layout title="Dashboard">
<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-800">Halo, {{ Str::before(auth()->user()->name, ' ') }}!</h1>
        <p class="text-sm text-gray-400 mt-1">{{ now()->translatedFormat('l, d F Y') }}</p>
    </div>

    {{-- Angka cepat --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-emerald-500">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Kunjungan hari ini</p>
            <p class="text-3xl font-extrabold text-gray-800 mt-1">{{ $kunjunganHariIni->count() }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-blue-500">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Aktivitas minggu ini</p>
            <p class="text-3xl font-extrabold text-gray-800 mt-1">{{ $mingguIni }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-orange-400">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Belum disentuh</p>
            <p class="text-3xl font-extrabold text-gray-800 mt-1">{{ $belumDisentuh }}</p>
            @if($belumDisentuh > 0)
                <p class="text-xs text-orange-500 mt-1">Lead baru, belum pernah di-follow-up</p>
            @endif
        </div>
    </div>

    {{-- Antrean tugas --}}
    <div class="bg-white rounded-xl shadow-sm">
        <div class="flex items-center justify-between p-4 border-b">
            <div>
                <h2 class="font-bold text-gray-700">Hubungi sekarang</h2>
                <p class="text-xs text-gray-400">5 lead paling mendesak — baru masuk atau paling lama diam</p>
            </div>
            <a href="{{ route('reaktivasi.index') }}" class="text-xs font-semibold text-emerald-600 hover:underline">Semua cold lead →</a>
        </div>
        <div class="divide-y">
            @forelse($tugasHariIni as $lead)
            <div class="flex items-center justify-between px-4 py-3 hover:bg-gray-50">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-gray-800">{{ $lead->name }}</span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $lead->statusColor() }}">{{ $lead->statusLabel() }}</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $lead->product?->name ?? 'Tanpa proyek' }}
                        · diam {{ $lead->daysSilent() }} hari
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0 ml-3">
                    @if($lead->wa_phone)
                    <a href="https://wa.me/{{ $lead->wa_phone }}" target="_blank"
                       class="text-xs bg-green-500 text-white px-3 py-1.5 rounded-lg hover:bg-green-600 font-medium">WhatsApp</a>
                    @endif
                    <a href="{{ route('leads.show', $lead) }}" class="text-xs border border-gray-300 px-3 py-1.5 rounded-lg text-gray-600 hover:bg-gray-50">Lihat</a>
                </div>
            </div>
            @empty
            <div class="px-4 py-8 text-center text-gray-400 text-sm">Semua lead sudah tersentuh! 🎉</div>
            @endforelse
        </div>
    </div>

    {{-- Corong pribadi --}}
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h2 class="font-bold text-gray-700 mb-4">Corong lead saya</h2>
        <div class="flex items-end gap-1" style="height: 120px;">
            @php $max = max(1, collect($corong)->max('total')); @endphp
            @foreach($corong as $c)
            <div class="flex-1 flex flex-col items-center justify-end h-full">
                <span class="text-sm font-extrabold text-gray-800">{{ $c['total'] }}</span>
                <div class="w-full rounded-t-lg bg-blue-500 mt-1 transition-all" style="height: {{ max(4, $c['total'] / $max * 100) }}%"></div>
                <span class="text-[10px] text-gray-500 mt-1.5 text-center leading-tight">{{ Str::limit($c['label'], 12) }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Kunjungan hari ini --}}
    @if($kunjunganHariIni->count() > 0)
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-4 border-b">
            <h2 class="font-bold text-gray-700">Kunjungan hari ini</h2>
        </div>
        <div class="divide-y">
            @foreach($kunjunganHariIni as $v)
            <div class="flex items-center gap-3 px-4 py-3">
                @if($v->photos->count() > 0)
                <img src="https://ekcsbcqvgmxweetoubze.supabase.co/storage/v1/object/public/kunjungan/{{ $v->photos->first()->photo_path }}" class="h-12 w-12 rounded-lg object-cover bg-gray-100">
                @else
                <div class="h-12 w-12 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 text-xs">📷</div>
                @endif
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800 truncate">{{ $v->client_name }}</p>
                    <p class="text-xs text-gray-500">{{ Str::title(str_replace('-', ' ', $v->project_slug)) }} · {{ $v->visited_at?->format('H:i') }}</p>
                </div>
                @php $mc = ['panas' => 'bg-red-100 text-red-700', 'hangat' => 'bg-amber-100 text-amber-700', 'dingin' => 'bg-slate-100 text-slate-600']; @endphp
                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase {{ $mc[$v->interest_level] ?? '' }}">{{ $v->interest_level }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
</x-app-layout>
