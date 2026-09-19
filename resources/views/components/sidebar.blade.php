<div class="w-64 bg-gray-900 text-white flex flex-col h-full">

    {{-- Logo --}}
    <div class="p-4 border-b border-gray-700">
        <h1 class="text-xl font-bold text-white">GAK CRM</h1>
        <p class="text-xs text-gray-400">AI Marketing Suite</p>
    </div>

    {{-- Menu --}}
    <nav class="flex-1 p-4 space-y-1 overflow-y-auto">

        <a href="/dashboard" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-700 {{ request()->is('dashboard') ? 'bg-gray-700' : '' }}">
            <span>📊</span><span class="text-sm">Dashboard</span>
        </a>

        <a href="/leads" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-700 {{ request()->is('leads*') ? 'bg-gray-700' : '' }}">
            <span>👥</span>
            <span class="text-sm">Leads</span>
            @php
            $user = auth()->user();
            // Jumlah lead yang perlu di-follow up (sesuai role lewat visibleTo)
            $newLeadsCount = \App\Models\Lead::visibleTo($user)->needsFollowUp()->count();
            @endphp
            @if($newLeadsCount > 0)
                <span class="ml-auto bg-orange-500 text-white text-xs font-bold px-2 py-0.5 rounded-full"
                      title="{{ $newLeadsCount }} lead perlu di-follow up">
                    {{ $newLeadsCount }}
                </span>
            @endif
        </a>

        <a href="/pipeline" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-700 {{ request()->is('pipeline*') ? 'bg-gray-700' : '' }}">
            <span>🔀</span><span class="text-sm">Pipeline</span>
        </a>

        <a href="/projects" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-700 {{ request()->is('projects*') ? 'bg-gray-700' : '' }}">
            <span>📁</span><span class="text-sm">Proyek</span>
        </a>

        <a href="/activities" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-700 {{ request()->is('activities*') ? 'bg-gray-700' : '' }}">
            <span>📅</span><span class="text-sm">Aktivitas</span>
        </a>

        <a href="/messages" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-700 {{ request()->is('messages*') ? 'bg-gray-700' : '' }}">
            <span>💬</span><span class="text-sm">WhatsApp Inbox</span>
            <span class="ml-auto text-xs bg-gray-600 text-gray-300 px-2 py-0.5 rounded-full">Soon</span>
        </a>

        <a href="/analytics" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-700 {{ request()->is('analytics*') ? 'bg-gray-700' : '' }}">
            <span>📈</span><span class="text-sm">Analytics</span>
        </a>

        <a href="/import" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-700 {{ request()->is('import*') ? 'bg-gray-700' : '' }}">
            <span>📥</span><span class="text-sm">Import Excel</span>
        </a>
        @if(auth()->user()->isDirektur())
        <a href="/users" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-700 {{ request()->is('users*') ? 'bg-gray-700' : '' }}">
            <span>👤</span><span class="text-sm">Manajemen User</span>
        </a>
        @endif

        @if(auth()->user()->isDirektur() || auth()->user()->isManajer())
        <a href="/team" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-700 {{ request()->is('team*') ? 'bg-gray-700' : '' }}">
            <span>👥</span><span class="text-sm">Team View</span>
        </a>
        <a href="{{ route('leads.conflicting') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-700 {{ request()->is('leads/conflicting') ? 'bg-gray-700' : '' }}">
            <span>⚠️</span><span class="text-sm">Lead Bentrok</span>
        </a>
        <a href="{{ route('leads.archived') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-700 {{ request()->is('leads/archived') ? 'bg-gray-700' : '' }}">
            <span>🗂️</span><span class="text-sm">Arsip Leads</span>
        </a>
        @endif
        <a href="/products-list" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-700 {{ request()->is('products-list*') ? 'bg-gray-700' : '' }}">
            <span>🏷️</span><span class="text-sm">Produk</span>
        </a>
        <a href="/reports" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-700 {{ request()->is('reports*') ? 'bg-gray-700' : '' }}">
            <span>📄</span><span class="text-sm">Laporan</span>
        </a>

    </nav>

    {{-- User Info --}}
    <div class="p-4 border-t border-gray-700">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-xs font-bold">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-400 truncate">{{ auth()->user()->email }}</p>
            </div>
        </div>
        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 text-xs text-gray-400 hover:text-white px-3 py-2 rounded-lg hover:bg-gray-700 mt-2 border border-gray-700 hover:border-gray-500">
            <span>⚙️</span><span>Pengaturan Profile</span>
        </a>
        <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf
            <button type="submit" class="w-full text-left text-xs text-gray-400 hover:text-white px-3 py-1 rounded hover:bg-gray-700">
                Logout
            </button>
        </form>
    </div>

</div>
