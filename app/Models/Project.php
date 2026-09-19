<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\ScopesByRole;

class Project extends Model
{
    use ScopesByRole;

    protected $fillable = [
        'name',
        'lead_id',
        'status',
        'priority',
        'value',
        'progress',
        'start_date',
        'due_date',
        'completed_date',
        'description',
        'assigned_to',
        'created_by',
    ];

    protected $casts = [
        'value'          => 'decimal:2',
        'progress'       => 'decimal:2',
        'start_date'     => 'date',
        'due_date'       => 'date',
        'completed_date' => 'date',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}