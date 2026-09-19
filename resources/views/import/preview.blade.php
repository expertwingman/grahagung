<x-app-layout>
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('import.index') }}" class="text-gray-400 hover:text-gray-600">← Kembali</a>
        <h1 class="text-2xl font-bold text-gray-800">Preview Data Import</h1>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
        <p class="text-sm text-gray-500 mb-1">Menampilkan <strong>10 baris pertama</strong> sebagai preview. Semua data akan diimport setelah konfirmasi.</p>
        <p class="text-xs text-gray-400">Kolom yang tidak dikenali akan diabaikan secara otomatis.</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left px-3 py-2 text-gray-500 font-medium">#</th>
                        @foreach($headers as $header)
                            <th class="text-left px-3 py-2 text-gray-500 font-medium">{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($preview as $i => $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 text-gray-400">{{ $i + 1 }}</td>
                        @foreach($row as $cell)
                            <td class="px-3 py-2 text-gray-700">{{ $cell }}</td>
                        @endforeach
                    </tr>
                    @empty
                    <tr>
                        <td colspan="99" class="px-4 py-6 text-center text-gray-400">Tidak ada data ditemukan</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex gap-3">
        <form method="POST" action="{{ route('import.process') }}">
            @csrf
            <button type="submit"
                class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 text-sm font-medium">
                Konfirmasi Import Semua Data
            </button>
        </form>
        <a href="{{ route('import.index') }}"
           class="border border-gray-300 text-gray-600 px-6 py-2 rounded-lg hover:bg-gray-50 text-sm">
            Batal
        </a>
    </div>
</x-app-layout>
