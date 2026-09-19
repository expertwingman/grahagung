<x-app-layout>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Manajemen User</h1>
            <p class="text-sm text-gray-400 mt-1">Kelola akun direktur, manajer, dan staff</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-3 gap-6">

        {{-- Form Tambah User --}}
        <div class="bg-white rounded-lg shadow-sm p-5">
            <h2 class="font-semibold text-gray-700 mb-4">Tambah User Baru</h2>
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Nama lengkap">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="email@domain.com">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">No. HP</label>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="08xxxxxxxxxx">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                        <select name="role" id="role-select" onchange="toggleManager()"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="staff" {{ old('role') == 'staff' ? 'selected' : '' }}>Staff / Sales</option>
                            <option value="manajer" {{ old('role') == 'manajer' ? 'selected' : '' }}>Manajer</option>
                            <option value="direktur" {{ old('role') == 'direktur' ? 'selected' : '' }}>Direktur</option>
                        </select>
                        @error('role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div id="manager-field">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Atasan (Manajer)</label>
                        <select name="manager_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Manajer --</option>
                            @foreach($managers as $manager)
                                <option value="{{ $manager->id }}" {{ old('manager_id') == $manager->id ? 'selected' : '' }}>
                                    {{ $manager->name }} ({{ ucfirst($manager->role) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Minimal 8 karakter">
                        @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Konfirmasi Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirmation"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Ulangi password">
                    </div>
                    <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm font-medium">
                        + Tambah User
                    </button>
                </div>
            </form>
        </div>

        {{-- Daftar User --}}
        <div class="col-span-2 space-y-4">

            {{-- Direktur --}}
            @php
                $direkturs = $users->where('role', 'direktur');
                $manajers  = $users->where('role', 'manajer');
                $staffs    = $users->where('role', 'staff');
            @endphp

            @foreach([['direktur', $direkturs, 'bg-purple-500', 'Direktur'], ['manajer', $manajers, 'bg-blue-500', 'Manajer'], ['staff', $staffs, 'bg-green-500', 'Staff / Sales']] as [$roleKey, $roleUsers, $color, $label])
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-4 border-b flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full {{ $color }}"></span>
                    <h2 class="font-semibold text-gray-700">{{ $label }}</h2>
                    <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full">{{ $roleUsers->count() }}</span>
                </div>
                @forelse($roleUsers as $user)
                <div class="flex items-center justify-between p-4 border-b border-gray-50 hover:bg-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 {{ $color }} rounded-full flex items-center justify-center text-white font-bold text-sm">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-medium text-gray-800">{{ $user->name }}</p>
                                @if($user->id === auth()->id())
                                    <span class="bg-yellow-100 text-yellow-700 text-xs px-2 py-0.5 rounded-full">Anda</span>
                                @endif
                                @if(!$user->is_active)
                                    <span class="bg-red-100 text-red-600 text-xs px-2 py-0.5 rounded-full">Non-aktif</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-400">{{ $user->email }}</p>
                            @if($user->manager)
                                <p class="text-xs text-gray-400">Atasan: {{ $user->manager->name }}</p>
                            @endif
                            @if($user->phone)
                                <p class="text-xs text-gray-400">{{ $user->phone }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="editUser({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ $user->email }}', '{{ $user->role }}', '{{ $user->phone }}', {{ $user->manager_id ?? 'null' }}, {{ $user->is_active ? 1 : 0 }})"
                            class="bg-yellow-50 text-yellow-600 border border-yellow-200 px-3 py-1 rounded text-xs font-medium hover:bg-yellow-100">
                            Edit
                        </button>
                        @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Hapus user {{ $user->name }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-50 text-red-600 border border-red-200 px-3 py-1 rounded text-xs font-medium hover:bg-red-100">
                                Hapus
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                @empty
                <div class="p-4 text-center text-gray-400 text-sm">Belum ada {{ $label }}</div>
                @endforelse
            </div>
            @endforeach

        </div>
    </div>

    {{-- Modal Edit User --}}
    <div id="modal-edit-user" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-800">Edit User</h2>
                <button onclick="document.getElementById('modal-edit-user').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" id="form-edit-user">
                @csrf
                @method('PUT')
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Nama</label>
                        <input type="text" name="name" id="edit-user-name"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" id="edit-user-email"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">No. HP</label>
                        <input type="text" name="phone" id="edit-user-phone"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Role</label>
                        <select name="role" id="edit-user-role"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="staff">Staff / Sales</option>
                            <option value="manajer">Manajer</option>
                            <option value="direktur">Direktur</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Atasan</label>
                        <select name="manager_id" id="edit-user-manager"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Tidak ada --</option>
                            @foreach($managers as $manager)
                                <option value="{{ $manager->id }}">{{ $manager->name }} ({{ ucfirst($manager->role) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Password Baru (kosongkan jika tidak diubah)</label>
                        <input type="password" name="password"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Minimal 8 karakter">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" id="edit-user-active" value="1" class="rounded">
                        <label for="edit-user-active" class="text-sm text-gray-700">User aktif</label>
                    </div>
                </div>
                <div class="flex gap-3 mt-4">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium flex-1">Update</button>
                    <button type="button" onclick="document.getElementById('modal-edit-user').classList.add('hidden')"
                        class="border border-gray-300 text-gray-600 px-4 py-2 rounded-lg text-sm flex-1">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function toggleManager() {
        const role = document.getElementById('role-select').value;
        const field = document.getElementById('manager-field');
        field.style.display = role === 'direktur' ? 'none' : 'block';
    }
    toggleManager();

    function editUser(id, name, email, role, phone, managerId, isActive) {
        document.getElementById('edit-user-name').value    = name;
        document.getElementById('edit-user-email').value   = email;
        document.getElementById('edit-user-role').value    = role;
        document.getElementById('edit-user-phone').value   = phone || '';
        document.getElementById('edit-user-active').checked = isActive == 1;
        document.getElementById('edit-user-manager').value = managerId || '';
        document.getElementById('form-edit-user').action   = '/users/' + id;
        document.getElementById('modal-edit-user').classList.remove('hidden');
    }
    </script>
</x-app-layout>
