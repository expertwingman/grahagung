<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tes ini menjaga aturan paling penting di CRM:
 * sales tidak boleh melihat atau mengubah data sales lain.
 *
 * Jalankan: php artisan test --filter=RoleAccessTest
 */
class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private function buatUser(string $role, ?int $managerId = null): User
    {
        return User::factory()->create([
            'role'       => $role,
            'manager_id' => $managerId,
            'is_active'  => true,
        ]);
    }

    private function buatLead(User $pemilik): Lead
    {
        return Lead::create([
            'name'        => 'Calon Pembeli',
            'status'      => 'no_respon',
            'assigned_to' => $pemilik->id,
            'created_by'  => $pemilik->id,
        ]);
    }

    public function test_staff_tidak_bisa_lihat_lead_staff_lain(): void
    {
        $staffA = $this->buatUser('staff');
        $staffB = $this->buatUser('staff');
        $lead   = $this->buatLead($staffB);

        $this->actingAs($staffA)
            ->get(route('leads.show', $lead))
            ->assertForbidden();
    }

    public function test_staff_tidak_bisa_edit_lead_staff_lain(): void
    {
        $staffA = $this->buatUser('staff');
        $staffB = $this->buatUser('staff');
        $lead   = $this->buatLead($staffB);

        $this->actingAs($staffA)
            ->get(route('leads.edit', $lead))
            ->assertForbidden();
    }

    public function test_staff_tidak_bisa_hapus_produk(): void
    {
        $staff = $this->buatUser('staff');

        $this->actingAs($staff)
            ->get(route('products.index'))
            ->assertOk();                 // lihat: boleh

        $this->actingAs($staff)
            ->post(route('products.store'), ['name' => 'Produk Bajakan'])
            ->assertForbidden();          // ubah: tidak boleh
    }

    public function test_staff_tidak_bisa_akses_import(): void
    {
        $staff = $this->buatUser('staff');

        $this->actingAs($staff)
            ->get(route('import.index'))
            ->assertForbidden();
    }

    public function test_manajer_bisa_lihat_lead_stafnya(): void
    {
        $manajer = $this->buatUser('manajer');
        $staff   = $this->buatUser('staff', $manajer->id);
        $lead    = $this->buatLead($staff);

        $this->actingAs($manajer)
            ->get(route('leads.show', $lead))
            ->assertOk();
    }

    public function test_manajer_tidak_bisa_lihat_lead_tim_lain(): void
    {
        $manajerA = $this->buatUser('manajer');
        $manajerB = $this->buatUser('manajer');
        $staffB   = $this->buatUser('staff', $manajerB->id);
        $lead     = $this->buatLead($staffB);

        $this->actingAs($manajerA)
            ->get(route('leads.show', $lead))
            ->assertForbidden();
    }

    public function test_akun_nonaktif_tidak_bisa_login(): void
    {
        $user = User::factory()->create([
            'role'      => 'staff',
            'is_active' => false,
            'password'  => 'password123',
        ]);

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_staff_tidak_bisa_menugaskan_lead(): void
    {
        $manajer = $this->buatUser('manajer');
        $staffA  = $this->buatUser('staff', $manajer->id);
        $staffB  = $this->buatUser('staff', $manajer->id);
        $lead    = $this->buatLead($staffA);

        $this->actingAs($staffA)
            ->post(route('leads.assign', $lead), ['assigned_to' => $staffB->id])
            ->assertForbidden();
    }

    public function test_manajer_bisa_menugaskan_lead_tanpa_pemilik(): void
    {
        $manajer = $this->buatUser('manajer');
        $staff   = $this->buatUser('staff', $manajer->id);

        $lead = Lead::create([
            'name'        => 'Lead Website',
            'status'      => 'no_respon',
            'source'      => 'website',
            'assigned_to' => null,
        ]);

        $this->actingAs($manajer)
            ->post(route('leads.assign', $lead), ['assigned_to' => $staff->id])
            ->assertRedirect();

        $this->assertSame($staff->id, $lead->fresh()->assigned_to);
    }

    public function test_manajer_tidak_bisa_menugaskan_ke_staff_tim_lain(): void
    {
        $manajerA = $this->buatUser('manajer');
        $manajerB = $this->buatUser('manajer');
        $staffB   = $this->buatUser('staff', $manajerB->id);

        $lead = Lead::create([
            'name'        => 'Lead Website',
            'status'      => 'no_respon',
            'source'      => 'website',
            'assigned_to' => null,
        ]);

        $this->actingAs($manajerA)
            ->post(route('leads.assign', $lead), ['assigned_to' => $staffB->id])
            ->assertSessionHasErrors('assigned_to');

        $this->assertNull($lead->fresh()->assigned_to);
    }

    public function test_reaktivasi_staff_hanya_lihat_lead_sendiri(): void
    {
        $manajer = $this->buatUser('manajer');
        $a = $this->buatUser('staff', $manajer->id);
        $b = $this->buatUser('staff', $manajer->id);

        $leadA = $this->buatLead($a); $leadA->forceFill(['name' => 'Lead Milik A Xyz', 'created_at' => now()->subDays(45)])->save();
        $leadB = $this->buatLead($b); $leadB->forceFill(['name' => 'Lead Milik B Qrs', 'created_at' => now()->subDays(45)])->save();

        $this->actingAs($a)->get(route('reaktivasi.index'))
            ->assertOk()
            ->assertSee('Lead Milik A Xyz')
            ->assertDontSee('Lead Milik B Qrs');
    }

    public function test_reaktivasi_lead_baru_tidak_dianggap_dingin(): void
    {
        $manajer = $this->buatUser('manajer');
        $a = $this->buatUser('staff', $manajer->id);
        $lead = $this->buatLead($a);
        $lead->forceFill(['name' => 'Lead Segar Hari Ini'])->save();

        $this->actingAs($a)->get(route('reaktivasi.index'))
            ->assertOk()
            ->assertDontSee('Lead Segar Hari Ini');
    }

    public function test_reaktivasi_catat_kontak_membuat_aktivitas(): void
    {
        $manajer = $this->buatUser('manajer');
        $a = $this->buatUser('staff', $manajer->id);
        $lead = $this->buatLead($a);
        $lead->forceFill(['created_at' => now()->subDays(45), 'status' => 'no_respon'])->save();

        $this->actingAs($a)
            ->post(route('reaktivasi.contacted', $lead), ['hasil' => 'tertarik'])
            ->assertRedirect();

        $lead->refresh();
        $this->assertSame('respon', $lead->status);
        $this->assertNotNull($lead->follow_up_date);
        $this->assertDatabaseHas('activities', [
            'subject_id' => $lead->id,
            'type' => 'whatsapp',
            'title' => 'Reaktivasi: Tertarik — lanjut',
        ]);
    }

    public function test_reaktivasi_staff_tidak_bisa_catat_lead_orang_lain(): void
    {
        $manajer = $this->buatUser('manajer');
        $a = $this->buatUser('staff', $manajer->id);
        $b = $this->buatUser('staff', $manajer->id);
        $leadB = $this->buatLead($b);

        $this->actingAs($a)
            ->post(route('reaktivasi.contacted', $leadB), ['hasil' => 'terkirim'])
            ->assertForbidden();
    }
}
