<x-app-layout title="Dashboard">
<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-800">Pusat Komando</h1>
        <p class="text-sm text-gray-400 mt-1">{{ now()->translatedFormat('l, d F Y') }}</p>
    </div>

    {{-- KPI --}}
    <div class="grid grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-blue-500">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Total lead</p>
            <p class="text-3xl font-extrabold text-gray-800 mt-1">{{ collect($corong)->sum('total') }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-yellow-500">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Pipeline aktif</p>
            <p class="text-3xl font-extrabold text-gray-800 mt-1">{{ $pipelineCount }}</p>
            <p class="text-xs text-yellow-600 mt-1">Rp {{ number_format($pipelineValue / 1000000, 1) }} jt</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-green-500">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Closing</p>
            <p class="text-3xl font-extrabold text-gray-800 mt-1">{{ $wonCount }}</p>
            <p class="text-xs text-green-600 mt-1">Rp {{ number_format($wonValue / 1000000, 1) }} jt</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-orange-400">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Cold lead</p>
            <p class="text-3xl font-extrabold text-gray-800 mt-1">{{ collect($coldPerSales)->sum('count') }}</p>
            <p class="text-xs text-orange-500 mt-1">Diam 30+ hari</p>
        </div>
    </div>

    {{-- Lead dari website --}}
    @include('dashboard._web_leads')

    {{-- Chart kompetitif + corong --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @include('dashboard._chart_kompetitif')
        @include('dashboard._corong', ['judul' => 'Corong seluruh perusahaan'])
    </div>

    {{-- Kontak per sales + cold lead --}}
    @php $staff = $allStaff; @endphp
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @include('dashboard._kontak_per_sales')
        @include('dashboard._cold_per_sales')
    </div>

    {{-- Kunjungan terbaru --}}
    @if($kunjunganTerbaru->count() > 0)
    <div class="bg-white rounded-xl shadow-sm">
        <div class="flex items-center justify-between p-4 border-b">
            <h2 class="font-bold text-gray-700">Kunjungan terbaru</h2>
            <a href="{{ route('kunjungan.index') }}" class="text-xs font-semibold text-blue-600 hover:underline">Semua →</a>
        </div>
        <div class="divide-y">
            @foreach($kunjunganTerbaru as $v)
            <div class="flex items-center gap-3 px-4 py-3">
                @if($v->photos->count() > 0)
                <img src="https://ekcsbcqvgmxweetoubze.supabase.co/storage/v1/object/public/kunjungan/{{ $v->photos->first()->photo_path }}" class="h-12 w-12 rounded-lg object-cover bg-gray-100">
                @else
                <div class="h-12 w-12 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 text-xs">📷</div>
                @endif
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800 truncate">{{ $v->client_name }}</p>
                    <p class="text-xs text-gray-500">{{ $v->user?->name }} · {{ Str::title(str_replace('-', ' ', $v->project_slug)) }} · {{ $v->visited_at?->diffForHumans() }}</p>
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
