<?php

namespace Tests\Feature\SuperAdmin;

use App\Livewire\Admin\Verifications\Index as VerificationIndex;
use App\Models\City;
use App\Models\Registration;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SuperAdminVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_superadmin_can_access_verification_page_and_view_national_records()
    {
        $cityJakarta = City::create(['name' => 'Kota Jakarta Selatan', 'province' => 'DKI Jakarta']);
        $citySurabaya = City::create(['name' => 'Kota Surabaya', 'province' => 'Jawa Timur']);

        $superadmin = User::factory()->create([
            'role' => 'super_admin',
            'email' => 'superadmin@sayabantu.com',
            'verified' => true,
            'status' => 'active',
        ]);

        $reg1 = Registration::create([
            'full_name' => 'Calon Mitra Jakarta',
            'email' => 'mitra.jakarta@test.com',
            'nik' => '3171010101900001',
            'city_id' => $cityJakarta->id,
            'city' => $cityJakarta->name,
            'province' => $cityJakarta->province,
            'status' => 'pending',
            'ktp_photo_path' => 'ktp/sample_ktp.jpg',
            'selfie_photo_path' => 'selfie/sample_selfie.jpg',
        ]);

        $reg2 = Registration::create([
            'full_name' => 'Calon Mitra Surabaya',
            'email' => 'mitra.surabaya@test.com',
            'nik' => '3578010101900002',
            'city_id' => $citySurabaya->id,
            'city' => $citySurabaya->name,
            'province' => $citySurabaya->province,
            'status' => 'pending',
            'ktp_photo_path' => 'ktp/sample_ktp2.jpg',
            'selfie_photo_path' => 'selfie/sample_selfie2.jpg',
        ]);

        $response = $this->actingAs($superadmin)->get(route('superadmin.verifications'));
        $response->assertStatus(200);
        $response->assertSee('Verifikasi KTP');
        $response->assertSee('Calon Mitra Jakarta');
        $response->assertSee('Calon Mitra Surabaya');

        // Test Livewire component approval by Super Admin
        Livewire::actingAs($superadmin)
            ->test(VerificationIndex::class)
            ->assertSee('Calon Mitra Jakarta')
            ->assertSee('Calon Mitra Surabaya')
            ->call('approveKtp', $reg1->id)
            ->assertSet('showModal', false);

        $this->assertEquals('approved', $reg1->fresh()->status);

        // Test Livewire component rejection by Super Admin
        Livewire::actingAs($superadmin)
            ->test(VerificationIndex::class)
            ->call('viewKtp', $reg2->id)
            ->assertSet('selectedId', $reg2->id)
            ->set('rejectReason', 'Foto buram')
            ->call('confirmReject', $reg2->id)
            ->assertSet('showModal', false);

        $this->assertEquals('rejected', $reg2->fresh()->status);
        $this->assertEquals('Foto buram', $reg2->fresh()->rejection_reason);
    }

    public function test_user_seeder_runs_successfully_with_env_credentials()
    {
        $this->seed(\Database\Seeders\CitySeeder::class);
        $this->seed(UserSeeder::class);

        $superadmin = User::where('email', 'superadmin@sayabantu.com')->first();
        $this->assertNotNull($superadmin);
        $this->assertEquals('super_admin', $superadmin->role);

        $admin = User::where('email', 'admin.jogja@sayabantu.com')->first();
        $this->assertNotNull($admin);
        $this->assertEquals('admin', $admin->role);
    }
}
