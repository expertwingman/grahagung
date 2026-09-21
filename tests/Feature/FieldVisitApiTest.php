<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * API aplikasi sales lapangan.
 * Schema "web" (proyek/tipe/unit) dibuat minimal di SQLite untuk tes.
 */
class FieldVisitApiTest extends TestCase
{
    use RefreshDatabase;

    private User $manajer;
    private User $sales;

    protected function setUp(): void
    {
        parent::setUp();

        // Tabel web.* tidak dibuat migrasi Laravel. Di SQLite, schema "web"
        // ditiru dengan ATTACH DATABASE sehingga "web"."projects" bisa dirujuk.
        try { DB::statement("ATTACH DATABASE ':memory:' AS web"); } catch (\Throwable) {}
        DB::statement('CREATE TABLE IF NOT EXISTS web.projects (id INTEGER PRIMARY KEY, slug TEXT, name TEXT, city TEXT)');
        DB::statement('CREATE TABLE IF NOT EXISTS web.unit_types (id INTEGER PRIMARY KEY, project_id INTEGER, slug TEXT, name TEXT, lb INTEGER, lt INTEGER, floors INTEGER)');
        DB::statement('CREATE TABLE IF NOT EXISTS web.units (id INTEGER PRIMARY KEY, project_id INTEGER, unit_type_id INTEGER, block TEXT, status TEXT)');
        DB::table('web.projects')->delete(); DB::table('web.unit_types')->delete(); DB::table('web.units')->delete();
        DB::table('web.projects')->insert(['id' => 1, 'slug' => 'wisata-semanggi', 'name' => 'Wisata Semanggi', 'city' => 'Surabaya']);
        DB::table('web.unit_types')->insert(['id' => 1, 'project_id' => 1, 'slug' => 'nitida-80', 'name' => 'Nitida', 'lb' => 80, 'lt' => 90, 'floors' => 2]);
        DB::table('web.units')->insert(['id' => 1, 'project_id' => 1, 'unit_type_id' => 1, 'block' => 'K.1-11', 'status' => 'available']);

        Storage::fake('supabase');

        $this->manajer = User::factory()->create(['role' => 'manajer', 'is_active' => true]);
        $this->sales   = User::factory()->create(['role' => 'staff', 'is_active' => true, 'manager_id' => $this->manajer->id]);
    }

    private function kunjungan(array $override = []): array
    {
        return array_merge([
            'phone'          => '081234567890',
            'name'           => 'Budi Santoso',
            'city'           => 'Surabaya',
            'source'         => 'iklan_meta',
            'came_with'      => 'pasangan',
            'occupation'     => 'swasta',
            'budget_range'   => '1500-2500',
            'payment_method' => 'kpr',
            'project_slug'   => 'wisata-semanggi',
            'unit_type_slug' => 'nitida-80',
            'unit_block'     => 'K.1-11',
            'interest_level' => 'panas',
            'next_action'    => 'tanda_jadi',
            'notes'          => 'Suka posisi hook.',
            'latitude'       => -7.31,
            'longitude'      => 112.81,
        ], $override);
    }

    public function test_catalog_mengembalikan_proyek_dan_tipe(): void
    {
        $this->actingAs($this->sales, 'sanctum')
            ->getJson('/api/catalog')
            ->assertOk()
            ->assertJsonPath('projects.0.slug', 'wisata-semanggi')
            ->assertJsonPath('projects.0.types.0.slug', 'nitida-80')
            ->assertJsonPath('projects.0.units.0', 'K.1-11');
    }

    public function test_kunjungan_baru_membuat_lead_visit_dan_aktivitas(): void
    {
        $r = $this->actingAs($this->sales, 'sanctum')
            ->postJson('/api/visits', $this->kunjungan())
            ->assertCreated()
            ->assertJsonPath('conflict', false);

        $lead = Lead::where('wa_phone', '6281234567890')->first();
        $this->assertNotNull($lead);
        $this->assertSame('survey', $lead->status);
        $this->assertSame($this->sales->id, $lead->assigned_to);
        $this->assertSame('swasta', $lead->occupation);
        $this->assertSame('kpr', $lead->payment_method);

        $visit = Visit::first();
        $this->assertSame($lead->id, $visit->lead_id);
        $this->assertSame('panas', $visit->interest_level);
        $this->assertNotNull($visit->server_captured_at);

        $this->assertDatabaseHas('activities', ['subject_id' => $lead->id, 'type' => 'meeting', 'status' => 'done']);
    }

    public function test_lead_lama_naik_status_ke_survey(): void
    {
        $lead = Lead::create(['name' => 'Budi', 'wa_phone' => '6281234567890', 'phone' => '6281234567890',
            'status' => 'kirim_pl', 'assigned_to' => $this->sales->id]);

        $this->actingAs($this->sales, 'sanctum')->postJson('/api/visits', $this->kunjungan())->assertCreated();

        $this->assertSame('survey', $lead->fresh()->status);
        $this->assertSame(1, Lead::count(), 'tidak boleh membuat lead ganda');
    }

    public function test_lead_milik_sales_lain_ditandai_bentrok_tapi_tetap_tercatat(): void
    {
        $lain = User::factory()->create(['role' => 'staff', 'is_active' => true, 'manager_id' => $this->manajer->id]);
        Lead::create(['name' => 'Budi', 'wa_phone' => '6281234567890', 'phone' => '6281234567890',
            'status' => 'respon', 'assigned_to' => $lain->id]);

        $this->actingAs($this->sales, 'sanctum')
            ->postJson('/api/visits', $this->kunjungan())
            ->assertCreated()
            ->assertJsonPath('conflict', true);

        $this->assertSame($lain->id, Lead::first()->assigned_to, 'kepemilikan tidak boleh berpindah diam-diam');
        $this->assertSame(1, Visit::count());
    }

    public function test_client_uuid_membuat_kiriman_ulang_idempoten(): void
    {
        $data = $this->kunjungan(['client_uuid' => 'abc-123']);
        $this->actingAs($this->sales, 'sanctum')->postJson('/api/visits', $data)->assertCreated();
        $this->actingAs($this->sales, 'sanctum')->postJson('/api/visits', $data)->assertOk()->assertJsonPath('duplicate', true);

        $this->assertSame(1, Visit::count());
    }

    public function test_lookup_menemukan_lead_dari_nomor_apa_pun_formatnya(): void
    {
        Lead::create(['name' => 'Budi', 'wa_phone' => '6281234567890', 'phone' => '6281234567890',
            'status' => 'respon', 'assigned_to' => $this->sales->id]);

        $this->actingAs($this->sales, 'sanctum')
            ->getJson('/api/leads/lookup?phone=' . urlencode('+62 812-3456-7890'))
            ->assertOk()
            ->assertJsonPath('found', true)
            ->assertJsonPath('mine', true)
            ->assertJsonPath('lead.name', 'Budi');
    }

    public function test_halaman_kunjungan_staff_hanya_lihat_miliknya(): void
    {
        $lain = User::factory()->create(['role' => 'staff', 'is_active' => true, 'manager_id' => $this->manajer->id]);
        $this->actingAs($this->sales, 'sanctum')->postJson('/api/visits', $this->kunjungan(['name' => 'Milik Sales A']))->assertCreated();
        $this->actingAs($lain, 'sanctum')->postJson('/api/visits', $this->kunjungan(['phone' => '0899000111222', 'name' => 'Milik Sales B']))->assertCreated();

        $this->actingAs($this->sales)->get(route('kunjungan.index'))
            ->assertOk()->assertSee('Milik Sales A')->assertDontSee('Milik Sales B');

        $this->actingAs($this->manajer)->get(route('kunjungan.index'))
            ->assertOk()->assertSee('Milik Sales A')->assertSee('Milik Sales B');
    }
}
