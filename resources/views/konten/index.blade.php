<x-app-layout title="Papan Konten">

<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Papan Konten</h1>
            <p class="text-sm text-gray-400 mt-1">Ide → Rencana → Produksi → Review → Publish</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('konten.export') }}" class="px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-600 hover:bg-gray-50">Export CSV</a>
            <a href="{{ route('konten.create') }}" class="px-4 py-2 rounded-lg bg-gray-800 text-sm font-medium text-white hover:bg-gray-700">+ Tambah Konten</a>
        </div>
    </div>

    {{-- Pipeline bar --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2">
        @foreach(\App\Models\ContentItem::STATUSES as $key => $label)
            @php $n = $perStatus[$key] ?? 0; $active = request('status') === $key; @endphp
            <a href="{{ route('konten.index', ['status' => $active ? '' : $key]) }}"
               class="rounded-xl p-3 text-center transition {{ $active ? 'bg-gray-800 text-white' : 'bg-white shadow-sm hover:bg-gray-50' }}">
                <div class="text-2xl font-extrabold">{{ $n }}</div>
                <div class="text-xs mt-0.5 {{ $active ? 'text-gray-300' : 'text-gray-500' }}">{{ $label }}</div>
            </a>
        @endforeach
    </div>

    {{-- Filter --}}
    <form class="flex flex-wrap gap-2 bg-white rounded-xl p-3 shadow-sm">
        <input name="q" value="{{ request('q') }}" placeholder="Cari judul / Content ID…"
               class="flex-1 min-w-[200px] rounded-lg border-gray-300 text-sm">
        <select name="platform" class="rounded-lg border-gray-300 text-sm">
            <option value="">Semua platform</option>
            @foreach(\App\Models\ContentItem::PLATFORMS as $k => $v)
                <option value="{{ $k }}" @selected(request('platform') === $k)>{{ $v }}</option>
            @endforeach
        </select>
        <button class="px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-600 hover:bg-gray-50">Filter</button>
        @if(request()->hasAny(['q', 'status', 'platform']))
            <a href="{{ route('konten.index') }}" class="px-3 py-2 text-sm text-gray-400 hover:text-gray-600">Reset</a>
        @endif
    </form>

    {{-- Tabel --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Konten</th>
                        <th class="px-4 py-3 text-left">Platform</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Jadwal</th>
                        <th class="px-4 py-3 text-left">Owner</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($contents as $c)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="font-semibold text-gray-800">{{ $c->title }}</div>
                            <div class="text-xs text-gray-400 mt-0.5 font-mono">{{ $c->content_id }}</div>
                            @if($c->product)
                                <div class="text-xs text-blue-600 mt-0.5">{{ $c->product->name }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-gray-700">{{ $c->platformLabel() }}</div>
                            <div class="text-xs text-gray-400">{{ $c->typeLabel() }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $c->statusColor() }}">{{ $c->statusLabel() }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $c->scheduled_at?->format('d M Y') ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $c->owner?->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('konten.edit', $c) }}" class="text-blue-600 hover:underline text-xs font-medium">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-400">
                            Belum ada konten. <a href="{{ route('konten.create') }}" class="text-blue-600 hover:underline">Mulai dari ide.</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t">{{ $contents->links() }}</div>
    </div>

    {{-- Kalender bulan ini --}}
    @if($kalender->count() > 0)
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h2 class="font-bold text-gray-700 mb-4">Jadwal konten</h2>
        <div class="space-y-2">
            @foreach($kalender->groupBy(fn($c) => $c->scheduled_at->format('d M Y')) as $tanggal => $items)
            <div class="flex gap-3">
                <div class="w-20 shrink-0 text-right">
                    <div class="text-sm font-bold text-gray-800">{{ \Carbon\Carbon::parse($tanggal)->format('d') }}</div>
                    <div class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('M') }}</div>
                </div>
                <div class="flex-1 space-y-1">
                    @foreach($items as $c)
                    <a href="{{ route('konten.edit', $c) }}"
                       class="flex items-center gap-2 rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 hover:bg-gray-100 transition">
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $c->statusColor() }}">{{ $c->statusLabel() }}</span>
                        <span class="text-sm text-gray-700 truncate flex-1">{{ $c->title }}</span>
                        <span class="text-xs text-gray-400">{{ $c->platformLabel() }}</span>
                        <span class="text-xs text-gray-400">{{ $c->scheduled_at->format('H:i') }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
</x-app-layout>
