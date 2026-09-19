<x-app-layout>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Manajemen Produk</h1>
            <p class="text-sm text-gray-400 mt-1">Kelola daftar produk/layanan untuk ketertarikan leads</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-3 gap-6">

        {{-- Form Tambah Produk --}}
        <div class="bg-white rounded-lg shadow-sm p-5">
            <h2 class="font-semibold text-gray-700 mb-4">Tambah Produk Baru</h2>
            <form method="POST" action="{{ route('products.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Contoh: Wisata Semanggi">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="description" rows="3"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Deskripsi singkat produk...">{{ old('description') }}</textarea>
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm font-medium">
                    + Tambah Produk
                </button>
            </form>
        </div>

        {{-- Daftar Produk --}}
        <div class="col-span-2 bg-white rounded-lg shadow-sm">
            <div class="p-4 border-b">
                <h2 class="font-semibold text-gray-700">Daftar Produk ({{ $products->count() }})</h2>
            </div>
            <div class="divide-y">
                @forelse($products as $product)
                <div class="p-4 flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3 flex-1">
                        <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center font-bold text-sm flex-shrink-0">
                            {{ strtoupper(substr($product->name, 0, 1)) }}
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <p class="font-medium text-gray-800">{{ $product->name }}</p>
                                @if($product->is_active)
                                    <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full">Aktif</span>
                                @else
                                    <span class="bg-gray-100 text-gray-500 text-xs px-2 py-0.5 rounded-full">Non-aktif</span>
                                @endif
                            </div>
                            @if($product->description)
                                <p class="text-xs text-gray-400 mt-1">{{ $product->description }}</p>
                            @endif
                            <p class="text-xs text-gray-400 mt-1">{{ $product->leads()->count() }} leads tertarik</p>
                        </div>
                    </div>
                    <div class="flex gap-2 flex-shrink-0">
                        <button onclick="editProduct({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ addslashes($product->description) }}', {{ $product->is_active ? 1 : 0 }})"
                            class="bg-yellow-50 text-yellow-600 border border-yellow-200 px-3 py-1 rounded text-xs font-medium hover:bg-yellow-100">
                            Edit
                        </button>
                        <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Hapus produk ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-50 text-red-600 border border-red-200 px-3 py-1 rounded text-xs font-medium hover:bg-red-100">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-gray-400">
                    Belum ada produk. Tambahkan produk pertama!
                </div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- Modal Edit --}}
    <div id="modal-edit" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-800">Edit Produk</h2>
                <button onclick="document.getElementById('modal-edit').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" id="form-edit">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk</label>
                    <input type="text" name="name" id="edit-name"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="description" id="edit-description" rows="3"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div class="mb-4 flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="edit-active" value="1" class="rounded">
                    <label for="edit-active" class="text-sm text-gray-700">Produk aktif</label>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium flex-1">Update</button>
                    <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')"
                        class="border border-gray-300 text-gray-600 px-4 py-2 rounded-lg text-sm flex-1">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function editProduct(id, name, description, isActive) {
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-description').value = description;
        document.getElementById('edit-active').checked = isActive == 1;
        document.getElementById('form-edit').action = '/products-list/' + id;
        document.getElementById('modal-edit').classList.remove('hidden');
    }
    </script>
</x-app-layout>
