<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visit extends Model
{
    protected $fillable = [
        'user_id',
        'client_id',
        'client_name',
        'client_phone',
        'client_email',
        'client_company',
        'notes',
        'status',
        'latitude',
        'longitude',
        'location_address',
        'visited_at',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
        'latitude'   => 'float',
        'longitude'  => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(VisitPhoto::class);
    }
}
