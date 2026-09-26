<x-app-layout title="{{ $content->exists ? 'Edit Konten' : 'Tambah Konten' }}">

<div class="max-w-4xl space-y-4">
    <a href="{{ route('konten.index') }}" class="text-sm text-blue-600 hover:underline">← Kembali ke papan konten</a>

    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ $content->exists ? 'Edit Konten' : 'Ide Baru' }}</h1>
        <p class="text-sm text-gray-400 mt-1">
            {{ $content->exists ? $content->content_id : 'Content ID dibuat otomatis saat disimpan.' }}
        </p>
    </div>

    <form method="POST"
          action="{{ $content->exists ? route('konten.update', $content) : route('konten.store') }}"
          class="space-y-5">
        @csrf
        @if($content->exists) @method('PUT') @endif

        <div class="bg-white rounded-xl shadow-sm p-5 space-y-4">

            {{-- Judul --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Judul</label>
                <input name="title" value="{{ old('title', $content->title) }}" required
                       class="w-full rounded-lg border-gray-300 text-base focus:border-blue-500 focus:ring-0"
                       placeholder="Judul konten atau ide…">
                @error('title') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                {{-- Proyek --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Proyek</label>
                    <select name="product_id" class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="">Tanpa proyek</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" @selected(old('product_id', $content->product_id) == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Status</label>
                    <select name="status" required class="w-full rounded-lg border-gray-300 text-sm">
                        @foreach(\App\Models\ContentItem::STATUSES as $k => $v)
                            <option value="{{ $k }}" @selected(old('status', $content->status ?? 'idea') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Platform --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Platform</label>
                    <select name="platform" class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="">Belum ditentukan</option>
                        @foreach(\App\Models\ContentItem::PLATFORMS as $k => $v)
                            <option value="{{ $k }}" @selected(old('platform', $content->platform) === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Jenis --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Jenis konten</label>
                    <select name="content_type" class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="">Belum ditentukan</option>
                        @foreach(\App\Models\ContentItem::TYPES as $k => $v)
                            <option value="{{ $k }}" @selected(old('content_type', $content->content_type) === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Owner --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Penanggung jawab</label>
                    <select name="owner_id" class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="">Diri sendiri</option>
                        @foreach($staff as $s)
                            <option value="{{ $s->id }}" @selected(old('owner_id', $content->owner_id) == $s->id)>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Jadwal --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Jadwal publish</label>
                    <input type="datetime-local" name="scheduled_at"
                           value="{{ old('scheduled_at', $content->scheduled_at?->format('Y-m-d\TH:i')) }}"
                           class="w-full rounded-lg border-gray-300 text-sm">
                </div>
            </div>

            {{-- Ide / brief --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Ide / Brief</label>
                <textarea name="idea" rows="3" placeholder="Apa yang ingin disampaikan?"
                          class="w-full rounded-lg border-gray-300 text-sm">{{ old('idea', $content->idea) }}</textarea>
            </div>

            {{-- Caption --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Caption / isi konten</label>
                <textarea name="caption" rows="6" placeholder="Draf caption atau isi konten…"
                          class="w-full rounded-lg border-gray-300 text-sm font-mono">{{ old('caption', $content->caption) }}</textarea>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Skor SEO (0–100)</label>
                    <input type="number" name="seo_score" min="0" max="100"
                           value="{{ old('seo_score', $content->seo_score) }}"
                           class="w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Skor Kualitas (0–100)</label>
                    <input type="number" name="quality_score" min="0" max="100"
                           value="{{ old('quality_score', $content->quality_score) }}"
                           class="w-full rounded-lg border-gray-300 text-sm">
                </div>
            </div>

            @if($content->exists && $content->status === 'published')
            <div class="border-t pt-4 mt-4">
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Tautan publikasi</label>
                <p class="text-xs text-gray-400 mb-2">Simpan URL setelah konten dipublikasikan di platform.</p>
                <div class="grid md:grid-cols-[1fr_2fr] gap-2">
                    <select name="pub_platform" class="rounded-lg border-gray-300 text-sm">
                        @foreach(\App\Models\ContentItem::PLATFORMS as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                    <input name="pub_url" placeholder="https://instagram.com/p/…" class="rounded-lg border-gray-300 text-sm">
                </div>
                @if($content->publications->count() > 0)
                <div class="mt-3 space-y-1">
                    @foreach($content->publications as $pub)
                    <div class="flex items-center gap-2 text-sm">
                        <span class="text-gray-500">{{ \App\Models\ContentItem::PLATFORMS[$pub->platform] ?? $pub->platform }}</span>
                        <a href="{{ $pub->published_url }}" target="_blank" class="text-blue-600 hover:underline truncate">{{ $pub->published_url }}</a>
                        <span class="text-xs text-gray-400">{{ $pub->published_at?->format('d M Y') }}</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endif
        </div>

        <div class="flex items-center gap-3">
            <button class="px-6 py-3 rounded-xl bg-gray-800 text-white font-bold text-sm hover:bg-gray-700">Simpan</button>
            @if($content->exists)
            <span class="text-xs text-gray-400">Terakhir diperbarui {{ $content->updated_at->diffForHumans() }}</span>
            @endif
        </div>
    </form>

    @if($content->exists && (auth()->user()->isDirektur() || auth()->user()->isManajer()))
    <form method="POST" action="{{ route('konten.destroy', $content) }}"
          onsubmit="return confirm('Hapus konten ini?')"
          class="mt-4">
        @csrf @method('DELETE')
        <button class="text-xs text-red-500 hover:underline">Hapus konten ini</button>
    </form>
    @endif
</div>
</x-app-layout>
