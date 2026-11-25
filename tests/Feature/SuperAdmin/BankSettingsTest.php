<?php

namespace Tests\Feature\SuperAdmin;

use App\Livewire\SuperAdmin\Settings\BankSettings;
use App\Models\AppSetting;
use App\Models\BankChannel;
use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class BankSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $city = City::create([
            'name' => 'Kota Yogyakarta',
            'province' => 'DI Yogyakarta',
            'is_active' => true,
        ]);

        $this->superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'active',
            'verified' => true,
            'is_completed' => true,
            'email_verified_at' => now(),
            'phone' => '081299999999',
            'nik' => '3171010101900001',
            'city_id' => $city->id,
        ]);
    }

    public function test_superadmin_can_access_bank_settings_page(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.settings.banks'))
            ->assertStatus(200)
            ->assertSee('Pengaturan Bank & E-Wallet', false)
            ->assertSee('Metode Pembayaran QRIS');
    }

    public function test_superadmin_can_add_bank_with_account_number_and_name(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test(BankSettings::class)
            ->call('openAddModal')
            ->set('code', 'TESTBANK')
            ->set('name', 'Bank Test Indonesia')
            ->set('category', 'bank')
            ->set('account_number', '9988776655')
            ->set('account_name', 'PT Rekening Test')
            ->set('order', 1)
            ->set('is_active', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('bank_channels', [
            'code' => 'TESTBANK',
            'name' => 'Bank Test Indonesia',
            'category' => 'bank',
            'account_number' => '9988776655',
            'account_name' => 'PT Rekening Test',
        ]);
    }

    public function test_superadmin_can_add_ewallet_with_phone_number(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test(BankSettings::class)
            ->call('openAddModal')
            ->set('code', 'TESTEWALLET')
            ->set('name', 'E-Wallet Test')
            ->set('category', 'ewallet')
            ->set('account_number', '089912345678')
            ->set('account_name', 'Kas Dompet Digital')
            ->set('order', 2)
            ->set('is_active', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('bank_channels', [
            'code' => 'TESTEWALLET',
            'category' => 'ewallet',
            'account_number' => '089912345678',
            'account_name' => 'Kas Dompet Digital',
        ]);
    }

    public function test_superadmin_can_update_qris_settings_and_upload_image(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('qris-test.png', 300, 300);

        Livewire::actingAs($this->superAdmin)
            ->test(BankSettings::class)
            ->set('qris_merchant_name', 'SayaBantu Official')
            ->set('qris_image_upload', $file)
            ->call('saveQrisSettings')
            ->assertHasNoErrors();

        $this->assertEquals('SayaBantu Official', AppSetting::get('qris_merchant_name'));
        $this->assertNotNull(AppSetting::get('qris_image'));
    }

    public function test_channel_order_is_automatically_assigned_and_resequenced(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test(BankSettings::class)
            ->call('openAddModal')
            ->set('code', 'AUTOBANK')
            ->set('name', 'Bank Otomatis Urut')
            ->set('category', 'bank')
            ->set('account_number', '11223344')
            ->call('save')
            ->assertHasNoErrors();

        $bank = BankChannel::where('code', 'AUTOBANK')->first();
        $this->assertNotNull($bank);
        $this->assertGreaterThan(0, $bank->order);
    }

    public function test_qris_save_and_bank_status_toggles_include_confirmation_prompts(): void
    {
        $channel = BankChannel::updateOrCreate(
            ['code' => 'BCA'],
            [
                'name' => 'Bank BCA',
                'category' => 'bank',
                'account_number' => '1234567890',
                'account_name' => 'PT SayaBantu',
                'is_active' => true,
                'order' => 1,
            ]
        );

        Livewire::actingAs($this->superAdmin)
            ->test(BankSettings::class)
            ->assertSee('wire:confirm="Apakah Anda yakin ingin menyimpan dan menerapkan pengaturan QRIS ini?"', false)
            ->assertSee('Apakah Anda yakin ingin menonaktifkan metode pembayaran QRIS?', false)
            ->assertSee('Apakah Anda yakin ingin menonaktifkan status saluran Bank BCA?', false);

        Livewire::actingAs($this->superAdmin)
            ->test(\App\Livewire\SuperAdmin\Settings\BiayaTopupBank::class)
            ->assertSee('wire:confirm="Apakah Anda yakin ingin menyimpan dan menerapkan pengaturan QRIS ini?"', false)
            ->assertSee('Apakah Anda yakin ingin menonaktifkan metode pembayaran QRIS?', false)
            ->assertSee('Apakah Anda yakin ingin menonaktifkan status rekening', false);
    }
}
