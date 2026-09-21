<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visit extends Model
{
    protected $fillable = [
        'user_id',
        'lead_id',
        'client_uuid',
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
        'server_captured_at',
        'project_slug',
        'unit_type_slug',
        'unit_block',
        'interest_level',
        'came_with',
        'next_action',
        'next_action_date',
        'payload',
    ];

    protected $casts = [
        'visited_at'         => 'datetime',
        'server_captured_at' => 'datetime',
        'next_action_date'   => 'date',
        'payload'            => 'array',
        'latitude'   => 'float',
        'longitude'  => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(VisitPhoto::class);
    }
}
