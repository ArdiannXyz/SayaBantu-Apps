<?php

namespace Tests\Feature;

use App\Livewire\Profile\UpdateProfileInformationForm;
use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Livewire\Volt\Volt;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private function user(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'customer',
            'status' => 'active',
            'verified' => true,
            'is_completed' => true,
            'email_verified_at' => now(),
        ], $attributes));
    }

    private function validProfileData(User $user, City $city): array
    {
        return [
            'name' => 'Nama Baru',
            'email' => $user->email,
            'nik' => '3402123456780001',
            'phone' => '081234567890',
            'city_id' => $city->id,
            'address' => 'Jl. Contoh No. 1',
            'gender' => 'Laki-laki',
        ];
    }

    public function test_profile_page_is_displayed(): void
    {
        $user = $this->user(['name' => 'Budi Santoso']);

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertSee('Budi Santoso');
    }

    public function test_profile_edit_page_renders_the_update_form(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->get('/profile/edit')
            ->assertOk()
            ->assertSeeLivewire(UpdateProfileInformationForm::class);
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = $this->user([
            'name' => 'Budi Santoso',
            'nik' => '3402123456780001',
            'gender' => null,
            'verified' => false,
        ]);
        $city = City::create(['name' => 'Kota Test', 'is_active' => true]);

        $this->actingAs($user);

        Livewire::test(UpdateProfileInformationForm::class)
            ->assertSet('isNameLocked', false)
            ->assertSet('isGenderLocked', false)
            ->set('name', 'Nama Baru')
            ->set('phone', '081234567890')
            ->set('city_id', $city->id)
            ->set('address', 'Jl. Contoh No. 1')
            ->set('gender', 'Laki-laki')
            ->call('updateProfileInformation')
            ->assertHasNoErrors();

        $user->refresh();

        $this->assertSame('Nama Baru', $user->name);
        $this->assertSame('3402123456780001', $user->nik);
        $this->assertSame('Laki-laki', $user->gender);
        $this->assertSame('081234567890', $user->phone);
        $this->assertSame($city->id, $user->city_id);
    }

    public function test_nik_cannot_be_changed_via_profile_update(): void
    {
        $city = City::create(['name' => 'Kota Test', 'is_active' => true]);
        $originalNik = '3402123456780001';
        $user = $this->user([
            'name' => 'Siti Aminah',
            'email' => 'saya@example.test',
            'nik' => $originalNik,
            'gender' => 'Perempuan',
            'verified' => false,
        ]);
        $this->actingAs($user);

        Livewire::test(UpdateProfileInformationForm::class)
            ->assertSet('isNameLocked', false)
            ->assertSet('isGenderLocked', true)
            ->set('name', 'Nama Baru')
            ->set('gender', 'Laki-laki')
            ->set('nik', '3402999999999999')
            ->set('phone', '081234567890')
            ->set('city_id', $city->id)
            ->set('address', 'Jl. Contoh No. 1')
            ->call('updateProfileInformation')
            ->assertHasNoErrors();

        $fresh = $user->fresh();
        // NIK dan Jenis Kelamin tetap terkunci, sementara Nama masih bisa diubah sebelum KTP terverifikasi
        $this->assertSame($originalNik, $fresh->nik);
        $this->assertSame('Nama Baru', $fresh->name);
        $this->assertSame('Perempuan', $fresh->gender);
    }

    public function test_name_is_locked_after_ktp_verification(): void
    {
        $city = City::create(['name' => 'Kota Test', 'is_active' => true]);
        $verifiedMitra = $this->user([
            'role' => 'mitra',
            'name' => 'Hendra Wijaya',
            'email' => 'hendra@example.test',
            'nik' => '3402123456780009',
            'gender' => 'Laki-laki',
            'verified' => true,
        ]);

        Livewire::actingAs($verifiedMitra)
            ->test(UpdateProfileInformationForm::class)
            ->assertSet('isNameLocked', true)
            ->assertSet('isGenderLocked', true)
            ->set('name', 'Nama Palsu')
            ->set('gender', 'Perempuan')
            ->set('phone', '081234567891')
            ->set('city_id', $city->id)
            ->set('address', 'Jl. Contoh No. 2')
            ->call('updateProfileInformation')
            ->assertHasNoErrors();

        $fresh = $verifiedMitra->fresh();
        $this->assertSame('Hendra Wijaya', $fresh->name);
        $this->assertSame('Laki-laki', $fresh->gender);
        $this->assertSame('081234567891', $fresh->phone);

        // Customer yang sudah terverifikasi KTP tetap dapat mengubah namanya
        $verifiedCustomer = $this->user([
            'role' => 'customer',
            'name' => 'Siti Awal',
            'email' => 'siti.verified@example.test',
            'nik' => '3402123456780010',
            'gender' => 'Perempuan',
            'verified' => true,
        ]);

        Livewire::actingAs($verifiedCustomer)
            ->test(UpdateProfileInformationForm::class)
            ->assertSet('isNameLocked', false)
            ->assertSet('isGenderLocked', true)
            ->set('name', 'Siti Baru')
            ->set('gender', 'Laki-laki')
            ->set('phone', '081234567892')
            ->set('city_id', $city->id)
            ->set('address', 'Jl. Contoh No. 3')
            ->call('updateProfileInformation')
            ->assertHasNoErrors();

        $freshCustomer = $verifiedCustomer->fresh();
        $this->assertSame('Siti Baru', $freshCustomer->name);
        $this->assertSame('Perempuan', $freshCustomer->gender);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = $this->user();

        $this->actingAs($user);

        $component = Volt::test('profile.delete-user-form')
            ->set('password', 'password')
            ->call('deleteUser');

        $component
            ->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = $this->user();

        $this->actingAs($user);

        $component = Volt::test('profile.delete-user-form')
            ->set('password', 'wrong-password')
            ->call('deleteUser');

        $component
            ->assertHasErrors('password')
            ->assertNoRedirect();

        $this->assertNotNull($user->fresh());
    }

    public function test_superadmin_and_admin_do_not_see_saldo_or_topup_on_profile(): void
    {
        $superadmin = $this->user(['name' => 'Superadmin User', 'role' => 'super_admin']);
        $admin = $this->user(['name' => 'Admin Wilayah User', 'role' => 'admin']);

        $this->actingAs($superadmin)
            ->get('/profile')
            ->assertOk()
            ->assertDontSee('Saldo / Wallet')
            ->assertDontSee('Top Up')
            ->assertDontSee('Pesanan Saya')
            ->assertDontSee('Metode Pembayaran')
            ->assertSee('Panel Super Admin');

        $this->actingAs($admin)
            ->get('/profile')
            ->assertOk()
            ->assertDontSee('Saldo / Wallet')
            ->assertDontSee('Top Up')
            ->assertDontSee('Pesanan Saya')
            ->assertDontSee('Metode Pembayaran')
            ->assertSee('Panel Admin Wilayah');
    }
}
