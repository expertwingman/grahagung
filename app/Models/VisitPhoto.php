<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Foto bukti kunjungan sales di lapangan.
 * Disimpan di Supabase Storage (disk: supabase).
 */
class VisitPhoto extends Model
{
    protected $fillable = [
        'visit_id',
        'photo_url',
        'photo_path',
        'caption',
    ];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }
}
