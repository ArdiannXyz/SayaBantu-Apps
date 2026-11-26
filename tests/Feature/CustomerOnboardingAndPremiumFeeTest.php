<?php

namespace Tests\Feature;

use App\Livewire\Customer\Helps\Create as CustomerHelpsCreate;
use App\Livewire\Profile\Verification;
use App\Livewire\SuperAdmin\Settings\FeePendapatan;
use App\Models\AppSetting;
use App\Models\Category;
use App\Models\City;
use App\Models\Help;
use App\Models\Registration;
use App\Models\User;
use App\Models\UserBalance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerOnboardingAndPremiumFeeTest extends TestCase
{
    use RefreshDatabase;

    private function createActiveCity(): City
    {
        return City::create([
            'name' => 'Kota Yogyakarta',
            'province' => 'DI Yogyakarta',
            'latitude' => -7.7956,
            'longitude' => 110.3695,
            'is_active' => true,
        ]);
    }

    public function test_customer_can_complete_registration_without_nik_or_ktp_and_create_help(): void
    {
        $city = $this->createActiveCity();
        $category = Category::create([
            'name' => 'Belanja & Titip',
            'icon' => '🛒',
            'is_active' => true,
        ]);

        // 1. Register as customer
        $this->post('/register', [
            'name' => 'Budi Santoso',
            'email' => 'budi.customer@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'customer',
        ])->assertRedirect(route('onboarding.step1'));

        // Step 1 view should not show KTP/NIK fields for customer
        $this->get(route('onboarding.step1'))
            ->assertOk()
            ->assertSee('Tanpa Upload KTP di Awal')
            ->assertDontSee('name="nik"', false);

        // 2. Submit step1 without NIK -> immediately completed & redirected to dashboard
        $response = $this->post(route('onboarding.step1.store'), [
            'name' => 'Budi Santoso',
            'gender' => 'Laki-laki',
            'phone' => '081234567891',
            'city_id' => $city->id,
            'address' => 'Jl. Kaliurang KM 5',
        ]);

        $response->assertRedirect(route('customer.dashboard'));

        $customer = User::where('email', 'budi.customer@example.com')->firstOrFail();
        $this->assertTrue((bool) $customer->is_completed);
        $this->assertFalse((bool) $customer->verified);
        $this->assertNull($customer->nik);
        $this->assertTrue($customer->isProfileComplete());
        $this->assertDatabaseMissing('registrations', ['email' => 'budi.customer@example.com']);

        // 3. Trying to visit KTP step2 redirects customer to dashboard
        $this->actingAs($customer)
            ->get(route('onboarding.step2'))
            ->assertRedirect(route('customer.dashboard'));

        // 4. Customer can access create help page without KTP verification blocker
        UserBalance::create([
            'user_id' => $customer->id,
            'balance' => 500000,
        ]);

        $this->actingAs($customer)
            ->get(route('customer.helps.create'))
            ->assertOk()
            ->assertDontSee('Verifikasi KTP Sedang Diproses');

        // 5. Submit help order as unverified customer -> uses regular customer fee (default 10%)
        Livewire::actingAs($customer)
            ->test(CustomerHelpsCreate::class)
            ->set('title', 'Antar Belanja Harian')
            ->set('category_id', (string) $category->id)
            ->set('amount', '100000')
            ->set('city_id', (string) $city->id)
            ->set('location', 'Depan Pasar')
            ->set('full_address', 'Jl. Kaliurang KM 5 No. 10')
            ->set('latitude', -7.7956)
            ->set('longitude', 110.3695)
            ->set('help_type', 'urgent')
            ->set('auto_cancel_minutes', 60)
            ->set('scheduled_date', now()->format('Y-m-d'))
            ->set('scheduled_time', now()->addHour()->format('H:i'))
            ->set('description', 'Tolong bantu antar belanja harian ke rumah')
            ->set('equipment_provided', 'sender')
            ->call('prepareConfirm')
            ->assertSet('showConfirmModal', true)
            ->assertSet('confirmCustomerFeePercent', 10.0)
            ->assertSet('confirmCustomerFee', 10000.0)
            ->call('save')
            ->assertHasNoErrors();

        $help = Help::where('user_id', $customer->id)->first();
        $this->assertNotNull($help);
        $this->assertSame(10.0, (float) $help->customer_fee_percent);
        $this->assertSame(10000.0, (float) $help->customer_fee_amount);
        $this->assertSame(110000.0, (float) $help->total_customer_paid);
    }

    public function test_mitra_registration_still_requires_nik_and_ktp_steps(): void
    {
        $city = $this->createActiveCity();

        $this->post('/register', [
            'name' => 'Siti Mitra',
            'email' => 'siti.mitra@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'mitra',
        ])->assertRedirect(route('onboarding.step1'));

        // Missing NIK fails validation for Mitra
        $this->from(route('onboarding.step1'))
            ->post(route('onboarding.step1.store'), [
                'name' => 'Siti Mitra',
                'gender' => 'Perempuan',
                'phone' => '081234567892',
                'city_id' => $city->id,
                'kelurahan' => 'Caturtunggal',
                'kecamatan' => 'Depok',
                'province' => 'DI Yogyakarta',
                'address' => 'Jl. Seturan Raya No. 2',
            ])
            ->assertSessionHasErrors('nik');

        // With valid NIK, Mitra proceeds to KTP step2
        $this->post(route('onboarding.step1.store'), [
            'nik' => '3404091234567890',
            'name' => 'Siti Mitra',
            'gender' => 'Perempuan',
            'phone' => '081234567892',
            'city_id' => $city->id,
            'kelurahan' => 'Caturtunggal',
            'kecamatan' => 'Depok',
            'province' => 'DI Yogyakarta',
            'address' => 'Jl. Seturan Raya No. 2',
        ])->assertRedirect(route('onboarding.step2'));
    }

    public function test_superadmin_can_configure_verified_customer_fee_and_premium_customer_gets_promo(): void
    {
        $city = $this->createActiveCity();
        $category = Category::create([
            'name' => 'Titip Beli',
            'icon' => '🛍️',
            'is_active' => true,
        ]);
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'active',
            'is_completed' => true,
        ]);
        $premiumCustomer = User::factory()->create([
            'role' => 'customer',
            'status' => 'active',
            'is_completed' => true,
            'verified' => true,
            'nik' => '3404099876543210',
            'ktp_photo' => 'ktp-photos/dummy.jpg',
            'selfie_photo' => 'selfie-photos/dummy.jpg',
            'phone' => '081234567893',
            'city_id' => $city->id,
            'city' => $city->name,
            'address' => 'Jl. Solo No. 8',
        ]);
        UserBalance::create([
            'user_id' => $premiumCustomer->id,
            'balance' => 500000,
        ]);

        // Default verified fee is 0%
        $this->assertSame(0.0, $premiumCustomer->getCustomerServiceFeePercent());

        // SuperAdmin sets regular customer fee = 10%, verified/premium fee = 0%
        Livewire::actingAs($superAdmin)
            ->test(FeePendapatan::class)
            ->set('customer_service_fee_percent', 10)
            ->set('customer_verified_service_fee_percent', 0)
            ->set('mitra_platform_fee_percent', 10)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('0', AppSetting::get('customer_verified_service_fee_percent'));

        Livewire::actingAs($premiumCustomer)
            ->test(CustomerHelpsCreate::class)
            ->set('title', 'Jemput Paket')
            ->set('category_id', (string) $category->id)
            ->set('amount', '100000')
            ->set('city_id', (string) $city->id)
            ->set('location', 'Kantor Pos')
            ->set('full_address', 'Jl. Solo No. 8')
            ->set('latitude', -7.7956)
            ->set('longitude', 110.3695)
            ->set('help_type', 'urgent')
            ->set('auto_cancel_minutes', 60)
            ->set('scheduled_date', now()->format('Y-m-d'))
            ->set('scheduled_time', now()->addHour()->format('H:i'))
            ->set('description', 'Bantu ambilkan paket di kantor pos terdekat')
            ->set('equipment_provided', 'sender')
            ->call('prepareConfirm')
            ->assertSet('showConfirmModal', true)
            ->assertSet('confirmCustomerFeePercent', 0.0)
            ->assertSet('confirmCustomerFee', 0.0)
            ->call('save')
            ->assertHasNoErrors();

        $help = Help::where('user_id', $premiumCustomer->id)->first();
        $this->assertNotNull($help);
        $this->assertSame(0.0, (float) $help->customer_fee_percent);
        $this->assertSame(0.0, (float) $help->customer_fee_amount);
        $this->assertSame(100000.0, (float) $help->total_customer_paid);

        // If SuperAdmin changes verified customer fee to 2.5%, model reflects 2.5%
        AppSetting::set('customer_verified_service_fee_percent', '2.5');
        $this->assertSame(2.5, $premiumCustomer->getCustomerServiceFeePercent());
    }

    public function test_unverified_customer_sees_premium_upgrade_in_profile_and_can_submit_nik_ktp(): void
    {
        Storage::fake('public');
        $city = $this->createActiveCity();

        $existingUser = User::factory()->create([
            'nik' => '3404011122233344',
            'city_id' => $city->id,
        ]);

        $customer = User::factory()->create([
            'role' => 'customer',
            'status' => 'active',
            'is_completed' => true,
            'verified' => false,
            'nik' => null,
            'ktp_photo' => null,
            'selfie_photo' => null,
            'phone' => '081234567894',
            'city_id' => $city->id,
            'city' => $city->name,
            'address' => 'Jl. Magelang No. 5',
        ]);

        // Profile displays Premium promo menu
        $this->actingAs($customer)
            ->get(route('profile'))
            ->assertOk()
            ->assertSee('Verifikasi KTP')
            ->assertSee('Rp 0');

        // Duplicate NIK is rejected
        Livewire::actingAs($customer)
            ->test(Verification::class)
            ->set('nik', $existingUser->nik)
            ->set('ktp_photo', UploadedFile::fake()->image('ktp.jpg', 600, 400))
            ->set('selfie_photo', UploadedFile::fake()->image('selfie.jpg', 600, 600))
            ->call('save')
            ->assertHasErrors('nik');

        // Valid unique 16-digit NIK + KTP + Selfie saves to user & creates pending_verification Registration
        Livewire::actingAs($customer)
            ->test(Verification::class)
            ->set('nik', '3404055566677788')
            ->set('ktp_photo', UploadedFile::fake()->image('ktp.jpg', 600, 400))
            ->set('selfie_photo', UploadedFile::fake()->image('selfie.jpg', 600, 600))
            ->call('save')
            ->assertHasNoErrors();

        $customer->refresh();
        $this->assertSame('3404055566677788', $customer->nik);
        $this->assertNotEmpty($customer->ktp_photo);
        $this->assertNotEmpty($customer->selfie_photo);

        $reg = Registration::where('email', $customer->email)->first();
        $this->assertNotNull($reg);
        $this->assertSame('3404055566677788', $reg->nik);
        $this->assertSame('pending_verification', $reg->status);
    }
}
