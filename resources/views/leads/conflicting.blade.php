<x-app-layout>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Lead Bentrok</h1>
            <p class="text-sm text-gray-500 mt-1">
                Calon pembeli dengan nomor WA sama yang dipegang lebih dari satu sales.
                Tinjau dan tentukan siapa yang menangani.
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

    @if($groups->isEmpty())
        <div class="bg-white rounded-lg shadow-sm p-8 text-center text-gray-500">
            <p class="text-lg mb-1">✓ Tidak ada lead bentrok</p>
            <p class="text-sm">Semua calon pembeli ditangani oleh satu sales masing-masing.</p>
        </div>
    @else
        <div class="mb-4 text-sm text-gray-600">
            Ditemukan <span class="font-semibold">{{ $groups->count() }}</span> nomor yang bentrok.
        </div>

        <div class="space-y-4">
            @foreach($groups as $phone => $leads)
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="bg-amber-50 border-b border-amber-200 px-4 py-3 flex items-center justify-between">
                        <div>
                            <span class="font-semibold text-amber-800">⚠ {{ $phone }}</span>
                            <span class="text-sm text-amber-600 ml-2">
                                dipegang {{ $leads->pluck('assigned_to')->unique()->count() }} sales
                            </span>
                        </div>
                        <span class="text-xs text-gray-500">{{ $leads->count() }} entri</span>
                    </div>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 border-b">
                                <th class="px-4 py-2 font-medium">Nama Customer</th>
                                <th class="px-4 py-2 font-medium">Sales</th>
                                <th class="px-4 py-2 font-medium">Produk</th>
                                <th class="px-4 py-2 font-medium">Status</th>
                                <th class="px-4 py-2 font-medium text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leads as $lead)
                                <tr class="border-b last:border-0">
                                    <td class="px-4 py-3 text-gray-800">{{ $lead->name }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $lead->assignedTo->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $lead->product->name ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $lead->statusColor() }}">
                                            {{ $lead->statusLabel() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('leads.edit', $lead) }}"
                                           class="text-blue-600 hover:underline">Tetapkan / Edit</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    @endif
</x-app-layout>
