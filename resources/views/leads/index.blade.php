<x-app-layout>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Leads</h1>
        <a href="{{ route('leads.create') }}"
           class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm font-medium">
            + Tambah Lead
        </a>
    </div>

    {{-- Search & Filter --}}
    <form method="GET" action="{{ route('leads.index') }}" class="bg-white rounded-lg shadow-sm p-4 mb-4">
        @if(request()->boolean('needs_followup'))
            <input type="hidden" name="needs_followup" value="1">
        @endif
        <div class="flex gap-3 items-end">
            <div class="flex-1">
                <label class="block text-xs text-gray-500 mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Nama, perusahaan, phone, email..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status</label>
                <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    @foreach(\App\Models\Lead::STATUSES as $key => $label)
                        <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Sumber</label>
                <select name="source" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Sumber</option>
                    @foreach($sources as $src)
                        <option value="{{ $src }}" {{ request('source') == $src ? 'selected' : '' }}>{{ $src }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm font-medium">
                    Cari
                </button>
                @if(request('search') || request('status') || request('source') || request('needs_followup'))
                    <a href="{{ route('leads.index') }}" class="border border-gray-300 text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-50 text-sm">
                        Reset
                    </a>
                @endif
            </div>
        </div>

        {{-- Filter cepat: perlu follow up --}}
        <div class="mt-3 pt-3 border-t border-gray-100">
            @if(request()->boolean('needs_followup'))
                <a href="{{ route('leads.index') }}"
                   class="inline-flex items-center gap-1 bg-orange-500 text-white px-3 py-1.5 rounded-lg text-sm font-medium hover:bg-orange-600">
                    ⏰ Perlu Follow Up — tampil semua
                </a>
            @else
                <a href="{{ route('leads.index', ['needs_followup' => 1]) }}"
                   class="inline-flex items-center gap-1 border border-orange-300 text-orange-700 px-3 py-1.5 rounded-lg text-sm font-medium hover:bg-orange-50">
                    ⏰ Tampilkan hanya yang perlu Follow Up
                </a>
            @endif
        </div>
    </form>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm">
            {{ session('success') }}
            @if(session('duplicateDetails') && count(session('duplicateDetails')) > 0)
                <details class="mt-2">
                    <summary class="cursor-pointer font-medium">Lihat daftar duplikat yang dilewati ({{ count(session('duplicateDetails')) }})</summary>
                    <ul class="mt-2 ml-4 list-disc text-green-800">
                        @foreach(session('duplicateDetails') as $dup)
                            <li>{{ $dup }}</li>
                        @endforeach
                    </ul>
                </details>
            @endif
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Nama</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Perusahaan</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Phone</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Sumber</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Status</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Nilai</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($leads as $lead)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-800">
                        {{ $lead->name }}
                        @if(str_starts_with($lead->name, 'Calon '))
                            <span class="ml-1 px-1.5 py-0.5 rounded text-xs bg-red-100 text-red-600 font-medium"
                                  title="Nama belum dilengkapi — klik Edit untuk update">
                                ! Nama
                            </span>
                        @endif
                        @if(in_array($lead->wa_phone, $conflictingPhones ?? []))
                            <a href="{{ route('leads.conflicting') }}"
                               title="Nomor ini dipegang lebih dari satu sales"
                               class="ml-1 inline-block px-1.5 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-700 hover:bg-amber-200">
                                ⚠ Bentrok
                            </a>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $lead->company ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $lead->phone ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $lead->source ?? '-' }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $lead->statusColor() }}">
                            {{ $lead->statusLabel() }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        {{ $lead->value ? 'Rp ' . number_format($lead->value, 0, ',', '.') : '-' }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex gap-1">
                            @if(!in_array($lead->status, ['closing','batal']))
                                <form method="POST" action="{{ route('leads.followed-up', $lead) }}">
                                    @csrf
                                    <button type="submit"
                                        title="Tandai sudah di-follow up hari ini"
                                        class="bg-green-50 text-green-700 border border-green-200 px-3 py-1 rounded text-xs font-medium hover:bg-green-100">
                                        ✓ Sudah FU
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('leads.show', $lead) }}"
                               class="bg-blue-50 text-blue-600 border border-blue-200 px-3 py-1 rounded text-xs font-medium hover:bg-blue-100">
                                Lihat
                            </a>
                            <a href="{{ route('leads.edit', $lead) }}"
                               class="bg-yellow-50 text-yellow-600 border border-yellow-200 px-3 py-1 rounded text-xs font-medium hover:bg-yellow-100">
                                Edit
                            </a>
                            <form method="POST" action="{{ route('leads.destroy', $lead) }}"
                                  onsubmit="return confirm('Hapus lead ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="bg-red-50 text-red-600 border border-red-200 px-3 py-1 rounded text-xs font-medium hover:bg-red-100">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                        Belum ada leads. <a href="{{ route('leads.create') }}" class="text-blue-600 hover:underline">Tambah sekarang</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($leads->hasPages())
            <div class="px-4 py-3 border-t">
                {{ $leads->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
