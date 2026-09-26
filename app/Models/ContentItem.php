<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentItem extends Model
{
    public const STATUSES = [
        'idea'       => 'Ide',
        'planned'    => 'Direncanakan',
        'production' => 'Produksi',
        'review'     => 'Review',
        'approved'   => 'Disetujui',
        'published'  => 'Dipublikasikan',
        'archived'   => 'Arsip',
    ];

    public const PLATFORMS = [
        'instagram' => 'Instagram',
        'tiktok'    => 'TikTok',
        'youtube'   => 'YouTube',
        'blog'      => 'Blog Website',
        'whatsapp'  => 'WhatsApp Broadcast',
        'facebook'  => 'Facebook',
    ];

    public const TYPES = [
        'reel'      => 'Reel / Short Video',
        'carousel'  => 'Carousel',
        'story'     => 'Story',
        'post'      => 'Post / Feed',
        'artikel'   => 'Artikel Blog',
        'broadcast' => 'Broadcast WA',
        'video'     => 'Video Panjang',
    ];

    protected $fillable = [
        'product_id', 'content_id', 'title', 'idea', 'caption',
        'platform', 'content_type', 'status',
        'scheduled_at', 'published_at',
        'owner_id', 'seo_score', 'quality_score', 'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function owner(): BelongsTo { return $this->belongsTo(User::class, 'owner_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function publications(): HasMany { return $this->hasMany(ContentPublication::class); }

    public function statusLabel(): string { return self::STATUSES[$this->status] ?? $this->status; }
    public function platformLabel(): string { return self::PLATFORMS[$this->platform] ?? $this->platform ?? '—'; }
    public function typeLabel(): string { return self::TYPES[$this->content_type] ?? $this->content_type ?? '—'; }

    public function statusColor(): string
    {
        return match ($this->status) {
            'idea'       => 'bg-slate-100 text-slate-700',
            'planned'    => 'bg-blue-100 text-blue-700',
            'production' => 'bg-amber-100 text-amber-700',
            'review'     => 'bg-purple-100 text-purple-700',
            'approved'   => 'bg-emerald-100 text-emerald-700',
            'published'  => 'bg-green-100 text-green-700',
            'archived'   => 'bg-gray-100 text-gray-500',
            default      => 'bg-gray-100 text-gray-700',
        };
    }

    /** Generate content ID: GAK-20260925-ABCD */
    public static function generateContentId(): string
    {
        return 'GAK-' . now()->format('YmdHis') . '-' . strtoupper(\Illuminate\Support\Str::random(4));
    }
}
