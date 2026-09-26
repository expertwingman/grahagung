<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentPublication extends Model
{
    protected $fillable = ['content_item_id', 'platform', 'published_url', 'published_at', 'status', 'notes'];
    protected $casts = ['published_at' => 'datetime'];

    public function contentItem(): BelongsTo { return $this->belongsTo(ContentItem::class); }
}
