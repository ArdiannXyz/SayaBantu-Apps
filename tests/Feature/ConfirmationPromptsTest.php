<?php

namespace Tests\Feature;

use App\Livewire\Admin\ChangePassword as AdminChangePassword;
use App\Livewire\Customer\Helps\Detail as CustomerHelpDetail;
use App\Livewire\Customer\TopupRequest;
use App\Livewire\Mitra\Profile\Edit as MitraProfileEdit;
use App\Livewire\Mitra\Settings\BankAccounts;
use App\Livewire\Profile\Verification;
use App\Livewire\SuperAdmin\AdminUsers;
use App\Livewire\SuperAdmin\Categories;
use App\Livewire\SuperAdmin\ChangePassword as SuperAdminChangePassword;
use App\Livewire\SuperAdmin\Settings\BankSettings;
use App\Models\City;
use App\Models\Help;
use App\Models\User;
use App\Models\WithdrawRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ConfirmationPromptsTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithCity(string $role, array $overrides = []): array
    {
        $city = City::firstOrCreate(
            ['name' => 'Jakarta Selatan'],
            ['province' => 'DKI Jakarta', 'is_active' => true]
        );

        $user = User::factory()->create(array_merge([
            'role' => $role,
            'city_id' => $city->id,
            'status' => 'active',
            'verified' => true,
            'is_completed' => true,
            'email_verified_at' => now(),
            'address' => 'Jl. Sudirman No. 1',
            'transaction_pin' => Hash::make('123456'),
        ], $overrides));

        return [$user, $city];
    }

    public function test_superadmin_pages_and_modals_include_confirmation_prompts(): void
    {
        [$superAdmin] = $this->createUserWithCity('super_admin', ['phone' => '081200009901']);
        [$customer] = $this->createUserWithCity('customer', ['phone' => '081200009902']);

        $withdraw = WithdrawRequest::create([
            'user_id' => $customer->id,
            'amount' => 50000,
            'bank_code' => 'BCA',
            'account_number' => '8888999900',
            'account_name' => $customer->name,
            'status' => WithdrawRequest::STATUS_PENDING,
        ]);

        $this->actingAs($superAdmin)
            ->get(route('superadmin.settings.banners'))
            ->assertOk()
            ->assertSee('wire:confirm="Apakah Anda yakin ingin mengunggah dan menyimpan banner beranda ini?"', false)
            ->assertSee('wire:confirm="Apakah Anda yakin ingin menyimpan dan menerapkan ikon baru aplikasi?"', false);

        Livewire::actingAs($superAdmin)
            ->test(Categories::class)
            ->call('openCreateModal')
            ->assertSeeHtml('wire:confirm="Apakah Anda yakin ingin menambahkan kategori baru ini?"');

        Livewire::actingAs($superAdmin)
            ->test(BankSettings::class)
            ->call('openAddModal')
            ->assertSeeHtml('wire:confirm="Apakah Anda yakin ingin menambahkan saluran pembayaran baru ini?"');

        Livewire::actingAs($superAdmin)
            ->test(AdminUsers::class)
            ->call('openCreateModal')
            ->assertSeeHtml('wire:confirm="Apakah Anda yakin ingin menyimpan akun admin baru ini?"');

        Livewire::actingAs($superAdmin)
            ->test(SuperAdminChangePassword::class)
            ->assertSeeHtml('wire:confirm="Apakah Anda yakin ingin mengubah kata sandi Super Admin?"');

        $this->actingAs($superAdmin)
            ->get(route('superadmin.cities'))
            ->assertOk()
            ->assertSee('wire:confirm="', false);

        $this->actingAs($superAdmin)
            ->get(route('superadmin.users'))
            ->assertOk()
            ->assertSee('wire:confirm="', false);

        $this->actingAs($superAdmin)
            ->get(route('superadmin.withdraws.show', $withdraw))
            ->assertOk()
            ->assertSee('data-confirm-btn="Ya, Setujui Transfer"', false);
    }

    public function test_admin_pages_include_confirmation_prompts(): void
    {
        [$admin, $city] = $this->createUserWithCity('admin', ['phone' => '081200009903']);
        $admin->managedCities()->sync([$city->id]);
        [$mitra] = $this->createUserWithCity('mitra', ['phone' => '081200009904']);

        $withdraw = WithdrawRequest::create([
            'user_id' => $mitra->id,
            'amount' => 75000,
            'bank_code' => 'BRI',
            'account_number' => '1122334455',
            'account_name' => $mitra->name,
            'status' => WithdrawRequest::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.withdraws.show', $withdraw))
            ->assertOk()
            ->assertSee('data-confirm-btn="Ya, Setujui Transfer"', false);

        Livewire::actingAs($admin)
            ->test(AdminChangePassword::class)
            ->assertSeeHtml('wire:confirm="Apakah Anda yakin ingin mengubah kata sandi Admin?"');
    }

    public function test_customer_and_mitra_pages_include_confirmation_prompts(): void
    {
        [$customer, $city] = $this->createUserWithCity('customer', ['phone' => '081200009905']);
        [$mitra] = $this->createUserWithCity('mitra', ['phone' => '081200009906']);

        $this->actingAs($customer)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('wire:confirm="Apakah Anda yakin ingin menyimpan perubahan informasi profil Anda?"', false);

        $this->actingAs($customer)
            ->get(route('customer.addresses.create'))
            ->assertOk()
            ->assertSee('wire:confirm="Apakah Anda yakin ingin menyimpan alamat baru ini?"', false);

        $this->assertStringContainsString(
            'wire:confirm="Apakah Anda yakin ingin menyimpan perubahan kata sandi akun Anda?"',
            file_get_contents(resource_path('views/livewire/profile/update-password-form.blade.php'))
        );

        Livewire::actingAs($customer)
            ->test(Verification::class)
            ->assertSeeHtml('wire:confirm="Apakah Anda yakin data NIK, foto KTP, dan foto selfie sudah benar dan siap dikirim untuk verifikasi?"');

        Livewire::actingAs($mitra)
            ->test(BankAccounts::class)
            ->call('openAddModal')
            ->assertSeeHtml('wire:confirm="Apakah Anda yakin ingin menyimpan rekening bank/e-wallet baru ini?"');

        Livewire::actingAs($mitra)
            ->test(MitraProfileEdit::class)
            ->call('openModal')
            ->assertSeeHtml('wire:confirm="Apakah Anda yakin ingin menyimpan perubahan profil mitra ini?"');

        Livewire::actingAs($customer)
            ->test(TopupRequest::class)
            ->set('currentStep', 3)
            ->assertSeeHtml('wire:confirm="Apakah Anda yakin data dan bukti transfer top-up sudah sesuai dan siap dikirim untuk diverifikasi admin?"');
    }
}
