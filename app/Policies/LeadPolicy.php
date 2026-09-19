<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    /**
     * Direktur bisa akses semua lead tanpa syarat.
     * Dipanggil otomatis sebelum method lain.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isDirektur()) {
            return true;
        }

        return null; // lanjut ke method di bawah
    }

    /**
     * Siapa yang boleh lihat daftar lead? Semua role.
     * (Filter per role sudah ditangani di LeadController::index)
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Siapa yang boleh lihat detail satu lead?
     * - Staff: hanya lead miliknya sendiri
     * - Manajer: lead miliknya + lead semua staff di bawahnya
     */
    public function view(User $user, Lead $lead): bool
    {
        if ($user->isStaff()) {
            return $lead->assigned_to === $user->id;
        }

        if ($user->isManajer()) {
            $staffIds = $user->staffMembers()->pluck('id')->toArray();
            $staffIds[] = $user->id;
            return in_array($lead->assigned_to, $staffIds);
        }

        return false;
    }

    /**
     * Siapa yang boleh membuat lead baru? Semua role.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Siapa yang boleh edit lead?
     * Aturan sama dengan view — tidak boleh edit data orang lain.
     */
    public function update(User $user, Lead $lead): bool
    {
        return $this->view($user, $lead);
    }

    /**
     * Siapa yang boleh hapus lead?
     * - Staff: tidak boleh hapus sama sekali
     * - Manajer: hanya lead yang masuk scope timnya
     */
    public function delete(User $user, Lead $lead): bool
    {
        if ($user->isStaff()) {
            return false;
        }

        if ($user->isManajer()) {
            $staffIds = $user->staffMembers()->pluck('id')->toArray();
            $staffIds[] = $user->id;
            return in_array($lead->assigned_to, $staffIds);
        }

        return false;
    }
}
