<div class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">

    {{-- Nama Halaman --}}
    <div>
        @php
            $titles = [
                'dashboard'  => 'Dashboard',
                'leads'      => 'Leads',
                'pipeline'   => 'Pipeline',
                'projects'   => 'Proyek',
                'activities' => 'Aktivitas',
                'import'     => 'Import Excel',
                'analytics'  => 'Analytics',
                'reports'    => 'Laporan',
            ];
            $segment = request()->segment(1) ?? 'dashboard';
            $title = $titles[$segment] ?? 'GAK CRM';
        @endphp
        <h2 class="text-sm font-semibold text-gray-800">{{ $title }}</h2>
        <p class="text-xs text-gray-400">{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
    </div>

    {{-- Kanan: Info User --}}
    <div class="flex items-center gap-3">
        <div class="w-7 h-7 bg-blue-500 rounded-full flex items-center justify-center text-xs font-bold text-white">
            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        </div>
        <div>
            <p class="text-xs font-medium text-gray-700">{{ auth()->user()->name }}</p>
            <p class="text-xs text-gray-400 capitalize">{{ auth()->user()->role }}</p>
        </div>
    </div>

</div>
