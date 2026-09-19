<x-app-layout>
    <div class="flex items-center gap-3 mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Import Leads dari Excel</h1>
    </div>

    @if(session('error'))
        <div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="font-semibold text-gray-700 mb-4">Upload File</h2>
            <form method="POST" action="{{ route('import.preview') }}" enctype="multipart/form-data">
                @csrf
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center mb-4 hover:border-blue-400 transition-colors">
                    <div class="text-4xl mb-2">📊</div>
                    <p class="text-sm text-gray-500 mb-2">Upload file Excel atau CSV</p>
                    <p class="text-xs text-gray-400 mb-4">Format: .xlsx, .xls, .csv — Maks 10MB</p>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    @error('file') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm font-medium">
                    Preview Data
                </button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="font-semibold text-gray-700 mb-4">Panduan Format Excel</h2>
            <p class="text-sm text-gray-500 mb-3">Pastikan baris pertama adalah header. Nama kolom yang didukung:</p>
            <div class="space-y-2">
                @foreach([
                    ['nama / name', 'Nama lead', true],
                    ['phone / no_hp / hp', 'Nomor HP', false],
                    ['email', 'Email', false],
                    ['perusahaan / company', 'Nama perusahaan', false],
                    ['sumber / source', 'Sumber lead', false],
                    ['kota / city', 'Kota', false],
                    ['alamat / address', 'Alamat', false],
                    ['catatan / notes', 'Catatan', false],
                ] as [$col, $desc, $required])
                <div class="flex items-center gap-3 text-sm">
                    <code class="bg-gray-100 px-2 py-0.5 rounded text-xs font-mono text-gray-700">{{ $col }}</code>
                    <span class="text-gray-500">{{ $desc }}</span>
                    @if($required)
                        <span class="text-red-500 text-xs">* wajib</span>
                    @endif
                </div>
                @endforeach
            </div>
            <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                <p class="text-xs text-blue-700">Nomor HP akan otomatis dikonversi ke format WhatsApp (628xxx)</p>
            </div>
        </div>
    </div>
</x-app-layout>
