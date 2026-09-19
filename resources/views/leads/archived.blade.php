<x-app-layout>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Arsip Leads</h1>
            <p class="text-sm text-gray-500 mt-1">
                Lead yang telah dihapus. Manajer dapat memulihkan, Direktur dapat menghapus permanen.
            </p>
        </div>
        <a href="{{ route('leads.index') }}"
           class="text-sm text-gray-500 hover:text-gray-700">&larr; Kembali ke Leads</a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if($leads->isEmpty())
        <div class="bg-white rounded-lg shadow-sm p-8 text-center text-gray-500">
            <p class="text-lg mb-1">✓ Arsip kosong</p>
            <p class="text-sm">Tidak ada lead yang diarsipkan.</p>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b text-left text-gray-600">
                        <th class="px-4 py-3 font-medium">Nama</th>
                        <th class="px-4 py-3 font-medium">Nomor WA</th>
                        <th class="px-4 py-3 font-medium">Produk</th>
                        <th class="px-4 py-3 font-medium">Sales</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Dihapus</th>
                        <th class="px-4 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leads as $lead)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-800">
                                {{ $lead->name }}
                                @if(str_starts_with($lead->name, 'Calon '))
                                    <span class="ml-1 px-1.5 py-0.5 rounded text-xs bg-red-100 text-red-600">! Nama</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $lead->wa_phone ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $lead->product->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $lead->assignedTo->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $lead->statusColor() }}">
                                    {{ $lead->statusLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">
                                {{ $lead->deleted_at->diffForHumans() }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    {{-- Pulihkan: manajer & direktur --}}
                                    <form method="POST" action="{{ route('leads.restore', $lead->id) }}">
                                        @csrf
                                        <button type="submit"
                                            class="text-xs bg-blue-50 text-blue-700 border border-blue-200 px-3 py-1 rounded hover:bg-blue-100">
                                            ↩ Pulihkan
                                        </button>
                                    </form>

                                    {{-- Hapus permanen: hanya direktur --}}
                                    @if(auth()->user()->isDirektur())
                                        <form method="POST" action="{{ route('leads.force-delete', $lead->id) }}"
                                              onsubmit="return confirm('Hapus permanen? Data tidak bisa dipulihkan lagi.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-xs bg-red-50 text-red-700 border border-red-200 px-3 py-1 rounded hover:bg-red-100">
                                                ✕ Hapus Permanen
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if($leads->hasPages())
                <div class="px-4 py-3 border-t">
                    {{ $leads->links() }}
                </div>
            @endif
        </div>
    @endif
</x-app-layout>
