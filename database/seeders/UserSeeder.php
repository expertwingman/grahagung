<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Direktur
        $direktur = $this->ensureUser('direktur@grahagung.com', [
            'name' => 'Nurhadi, S.E.',
            'role' => 'direktur',
        ]);

        // 2. Manajer (di bawah direktur)
        $manajer = $this->ensureUser('manajer@grahagung.com', [
            'name'       => 'Wisnu',
            'role'       => 'manajer',
            'manager_id' => $direktur->id,
        ]);

        // 3. Staff sales (semua di bawah manajer Wisnu)
        $staff = ['Luluk', 'Andri', 'Adjie', 'Aditya', 'Avit', 'Wahyu', 'Tony', 'Jervis'];
        foreach ($staff as $nama) {
            $this->ensureUser(strtolower($nama) . '@grahagung.com', [
                'name'       => $nama,
                'role'       => 'staff',
                'manager_id' => $manajer->id,
            ]);
        }

        $this->command->info('User: 1 direktur, 1 manajer, ' . count($staff) . ' staff tersedia.');
    }

    /**
     * Buat user kalau belum ada (password default "password" HANYA untuk user baru).
     * Kalau user sudah ada, perbarui data non-sensitif TANPA menyentuh password.
     */
    private function ensureUser(string $email, array $attrs): User
    {
        $existing = User::where('email', $email)->first();

        if ($existing) {
            // Jangan timpa password user yang sudah aktif
            $existing->update($attrs);
            return $existing;
        }

        return User::create(array_merge($attrs, [
            'email'     => $email,
            'password'  => Hash::make('password'),
            'is_active' => true,
        ]));
    }
}
