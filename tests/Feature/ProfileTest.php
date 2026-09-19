<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_profil_tampil(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)->get('/profile')->assertOk();
    }

    public function test_profil_bisa_diperbarui(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)
            ->put('/profile', [
                'name'  => 'Nama Baru',
                'email' => 'baru@grahagung.com',
                'phone' => '628123456789',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Nama Baru', $user->name);
        $this->assertSame('baru@grahagung.com', $user->email);
    }

    public function test_password_bisa_diubah(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'password'  => 'password-lama',
        ]);

        $this->actingAs($user)
            ->put('/profile/password', [
                'current_password'      => 'password-lama',
                'password'              => 'password-baru',
                'password_confirmation' => 'password-baru',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertTrue(
            \Hash::check('password-baru', $user->refresh()->password)
        );
    }

    public function test_password_lama_harus_benar(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'password'  => 'password-lama',
        ]);

        $this->actingAs($user)
            ->put('/profile/password', [
                'current_password'      => 'password-salah',
                'password'              => 'password-baru',
                'password_confirmation' => 'password-baru',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(
            \Hash::check('password-lama', $user->refresh()->password)
        );
    }
}
