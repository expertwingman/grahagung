@php $u = auth()->user(); @endphp
<div class="w-64 bg-gray-900 text-white flex flex-col h-full">

    {{-- Logo --}}
    <div class="p-4 border-b border-gray-700">
        <h1 class="text-xl font-bold text-white">GAK CRM</h1>
        <p class="text-xs text-gray-400">AI Marketing Suite</p>
    </div>

    <nav class="flex-1 p-4 space-y-1 overflow-y-auto">

        {{-- SEMUA PERAN --}}
        <a href="/dashboard" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-700 {{ request()->is('dashboard') ? 'bg-gray-700' : '' }}">
            <span>📊</span><span class="text-sm">Dashboard</span>
        </a>

        <a href="/leads" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-700 {{ request()->is('leads*') && !request()->is('leads/conflicting') && !request()->is('leads/archived') ? 'bg-gray-700' : '' }}">
            <span>👥</span><span class="text-sm">Leads</span>
        </a>

        <a href="/activities" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-700 {{ request()->is('activities*') ? 'bg-gray-700' : '' }}">
            <span>📅</span><span class="text-sm">Aktivitas</span>
        </a>

        {{-- ===== MESIN LEAD ===== --}}
        <div class="mt-4 mb-1 px-3 text-[10px] font-bold uppercase tracking-[0.2em] text-emerald-400">Mesin Lead</div>

        <a href="{{ route('kunjungan.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-700 {{ request()->is('kunjungan*') ? 'bg-gray-700' : '' }}">
            <span>📍</span><span class="text-sm">Kunjungan Lapangan</span>
        </a>

        <a href="{{ route('konten.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-700 {{ request()->is('konten*') ? 'bg-gray-700' : '' }}">
            <span>📝</span><span class="text-sm">Papan Konten</span>
        </a>

        <a href="{{ route('reaktivasi.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-700 {{ request()->is('reaktivasi*') ? 'bg-gray-700' : '' }}">
            <span>♻️</span><span class="text-sm">Reaktivasi Cold Lead</span>
        </a>

        {{-- ===== MANAJER & DIREKTUR ===== --}}
        @if($u->isDirektur() || $u->isManajer())
        <div class="mt-4 mb-1 px-3 text-[10px] font-bold uppercase tracking-[0.2em] text-blue-400">Manajemen</div>

        <a href="/team" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-700 {{ request()->is('team*') ? 'bg-gray-700' : '' }}">
            <span>👥</span><span class="text-sm">Team View</span>
        </a>

        <a href="{{ route('leads.conflicting') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-700 {{ request()->is('leads/conflicting') ? 'bg-gray-700' : '' }}">
            <span>⚠️</span><span class="text-sm">Lead Bentrok</span>
        </a>

        <a href="{{ route('leads.archived') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-700 {{ request()->is('leads/archived') ? 'bg-gray-700' : '' }}">
            <span>🗂️</span><span class="text-sm">Arsip Leads</span>
        </a>

        <a href="/import" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-700 {{ request()->is('import*') ? 'bg-gray-700' : '' }}">
            <span>📥</span><span class="text-sm">Import Excel</span>
        </a>
        @endif

        {{-- ===== DIREKTUR ONLY ===== --}}
        @if($u->isDirektur())
        <div class="mt-4 mb-1 px-3 text-[10px] font-bold uppercase tracking-[0.2em] text-amber-400">Pengaturan</div>

        <a href="/users" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-700 {{ request()->is('users*') ? 'bg-gray-700' : '' }}">
            <span>👤</span><span class="text-sm">Manajemen User</span>
        </a>

        <a href="/products-list" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-700 {{ request()->is('products-list*') ? 'bg-gray-700' : '' }}">
            <span>🏷️</span><span class="text-sm">Produk</span>
        </a>
        @endif

    </nav>

    {{-- User Info --}}
    <div class="p-4 border-t border-gray-700">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-xs font-bold">
                {{ strtoupper(substr($u->name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium truncate">{{ $u->name }}</p>
                <p class="text-xs text-gray-400 truncate">{{ ucfirst($u->role) }}</p>
            </div>
        </div>
        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 text-xs text-gray-400 hover:text-white px-3 py-2 rounded-lg hover:bg-gray-700 mt-2 border border-gray-700 hover:border-gray-500">
            <span>⚙️</span><span>Pengaturan Profile</span>
        </a>
        <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf
            <button type="submit" class="w-full text-left text-xs text-gray-400 hover:text-white px-3 py-1 rounded hover:bg-gray-700">Logout</button>
        </form>
    </div>
</div>
