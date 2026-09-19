<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Trait RBAC untuk model yang punya kolom 'assigned_to'.
 *
 * Pakai di model: use ScopesByRole;
 * Lalu di query:  Lead::visibleTo($user)->count();
 *
 * Aturan:
 * - Direktur : lihat semua data
 * - Manajer  : lihat data miliknya + semua staff di bawahnya
 * - Staff    : hanya data miliknya sendiri
 */
trait ScopesByRole
{
    public function scopeVisibleTo(Builder $query, ?User $user = null): Builder
    {
        $user ??= auth()->user();

        // Tidak ada user (mis. dipanggil dari console) → jangan bocorkan apa-apa
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isDirektur()) {
            return $query; // semua data
        }

        if ($user->isManajer()) {
            $ids   = $user->staffMembers()->pluck('id')->toArray();
            $ids[] = $user->id;
            return $query->whereIn('assigned_to', $ids);
        }

        // Staff (atau role lain) → hanya miliknya
        return $query->where('assigned_to', $user->id);
    }
}
