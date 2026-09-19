<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Profil Saya</h1>
        <p class="text-sm text-gray-400 mt-1">Kelola informasi akun dan keamanan Anda</p>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-2 gap-6">

        {{-- Info Profil --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <span class="w-1 h-5 bg-blue-500 rounded"></span>
                Informasi Profil
            </h2>

            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">No. HP</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="08xxxxxxxxxx">
                    </div>

                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-500">Role</p>
                        <p class="text-sm font-medium text-gray-800 mt-1">
                            @if($user->isDirektur())
                                <span class="bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full text-xs">Direktur</span>
                            @elseif($user->isManajer())
                                <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-xs">Manajer</span>
                            @else
                                <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">Staff / Sales</span>
                            @endif
                        </p>
                    </div>

                    @if($user->manager)
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-500">Atasan</p>
                        <p class="text-sm font-medium text-gray-800 mt-1">{{ $user->manager->name }}</p>
                    </div>
                    @endif

                    <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm font-medium">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        {{-- Ganti Password --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <span class="w-1 h-5 bg-red-500 rounded"></span>
                Ganti Password
            </h2>

            <form method="POST" action="{{ route('profile.password') }}">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password Lama</label>
                        <input type="password" name="current_password"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Masukkan password lama">
                        @error('current_password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                        <input type="password" name="password"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Minimal 8 karakter">
                        @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Ulangi password baru">
                    </div>

                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                        <p class="text-xs text-yellow-700">⚠️ Setelah ganti password, Anda akan tetap login. Gunakan password baru untuk login berikutnya.</p>
                    </div>

                    <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 text-sm font-medium">
                        Ganti Password
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
