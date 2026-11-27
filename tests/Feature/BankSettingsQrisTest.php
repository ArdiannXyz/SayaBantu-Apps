<?php

namespace Tests\Feature;

use App\Livewire\Customer\Helps\Create as CreateHelp;
use App\Livewire\Customer\TopupRequest as TopupRequestComponent;
use App\Livewire\SuperAdmin\Settings\BankSettings;
use App\Models\AppSetting;
use App\Models\BankChannel;
use App\Models\Category;
use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class BankSettingsQrisTest extends TestCase
{
    use RefreshDatabase;

    public function test_app_setting_forget_deletes_key(): void
    {
        AppSetting::set('qris_image', 'payment/sample.png');
        $this->assertSame('payment/sample.png', AppSetting::get('qris_image'));

        $deleted = AppSetting::forget('qris_image');

        $this->assertSame(1, $deleted);
        $this->assertNull(AppSetting::get('qris_image'));
    }

    public function test_super_admin_can_remove_qris_image_without_error(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('payment/test-qris.png', 'fake-image-content');
        AppSetting::set('qris_image', 'payment/test-qris.png');

        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'active',
            'is_completed' => true,
        ]);

        Livewire::actingAs($superAdmin)
            ->test(BankSettings::class)
            ->assertSet('current_qris_image', 'payment/test-qris.png')
            ->call('removeQrisImage')
            ->assertSet('current_qris_image', null)
            ->assertHasNoErrors();

        $this->assertFalse(Storage::disk('public')->exists('payment/test-qris.png'));
        $this->assertNull(AppSetting::get('qris_image'));
    }

    public function test_create_help_modal_and_topup_page_sync_with_superadmin_qris_settings(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('payment/custom-qris-baru.png', 'new-qris-bytes');
        AppSetting::set('qris_image', 'payment/custom-qris-baru.png');
        AppSetting::set('qris_merchant_name', 'Warung Berkah QR');

        $bank = BankChannel::updateOrCreate(['code' => 'BCA'], [
            'name' => 'Bank BCA',
            'category' => 'bank',
            'account_number' => '1122334455',
            'account_name' => 'PT SayaBantu',
            'is_active' => true,
            'order' => 1,
        ]);

        AppSetting::set('topup_payment_methods', json_encode([
            'qris' => ['enabled' => true, 'merchant_name' => 'Warung Berkah QR'],
            'banks' => [[
                'code' => strtolower($bank->code),
                'name' => $bank->name,
                'account_number' => $bank->account_number,
                'account_name' => $bank->account_name,
                'enabled' => true,
            ]],
        ]));

        $city = City::create(['name' => 'Yogyakarta', 'province' => 'DIY', 'type' => 'kota']);
        Category::create(['name' => 'Kebersihan', 'slug' => 'kebersihan']);

        $customer = User::factory()->create([
            'name' => 'Customer QRIS',
            'role' => 'customer',
            'status' => 'active',
            'is_completed' => true,
            'verified' => true,
            'city_id' => $city->id,
            'phone' => '081234567890',
            'address' => 'Jl. Malioboro No. 1',
            'gender' => 'male',
            'email_verified_at' => now(),
            'balance' => 0,
        ]);

        $expectedUrlPart = 'storage/payment/custom-qris-baru.png?v=';

        Livewire::actingAs($customer)
            ->test(TopupRequestComponent::class)
            ->set('amount', 50000)
            ->set('currentStep', 3)
            ->call('selectPaymentMethod', 'qris')
            ->assertSee($expectedUrlPart)
            ->assertSee('Warung Berkah QR');

        Livewire::actingAs($customer)
            ->test(CreateHelp::class)
            ->set('showInsufficientModal', true)
            ->call('loadPaymentSettings')
            ->assertSet('topupMethod', 'qris')
            ->assertSee($expectedUrlPart)
            ->assertSee('Warung Berkah QR');

        // When QRIS is disabled in BankSettings, CreateHelp switches away from 'qris'
        AppSetting::set('topup_payment_methods', json_encode([
            'qris' => ['enabled' => false, 'merchant_name' => 'Warung Berkah QR'],
            'banks' => [[
                'code' => 'bca',
                'name' => 'Bank BCA',
                'account_number' => '1122334455',
                'account_name' => 'PT SayaBantu',
                'enabled' => true,
            ]],
        ]));

        Livewire::actingAs($customer)
            ->test(CreateHelp::class)
            ->set('showInsufficientModal', true)
            ->call('loadPaymentSettings')
            ->assertSet('qrisEnabled', false)
            ->assertSet('topupMethod', 'bank_bca')
            ->assertDontSee($expectedUrlPart);
    }

    public function test_topup_payment_config_does_not_fall_back_to_dummy_bank_accounts(): void
    {
        AppSetting::forget('topup_payment_methods');
        BankChannel::query()->update(['is_active' => false]);

        $config = AppSetting::activePaymentConfig();

        $this->assertTrue($config['qris_enabled']);
        $this->assertSame([], $config['banks']);

        // Activating one real BankChannel feeds it without needing topup_payment_methods cache
        BankChannel::where('code', 'BCA')->update([
            'is_active' => true,
            'account_number' => '9988776655',
            'account_name' => 'PT SayaBantu Resmi',
        ]);

        $activeConfig = AppSetting::activePaymentConfig();
        $this->assertCount(1, $activeConfig['banks']);
        $this->assertSame('9988776655', $activeConfig['banks'][0]['account_number']);
        $this->assertSame('PT SayaBantu Resmi', $activeConfig['banks'][0]['account_name']);
    }

    public function test_topup_admin_fee_uses_superadmin_biaya_topup_defaults_and_overrides(): void
    {
        AppSetting::forget('topup_tier1_fee');
        AppSetting::forget('topup_tier1_limit');

        // Default BiayaTopupBank tier1 fee is 5000 (not legacy 9000)
        $this->assertSame(5000, AppSetting::calculateTopupAdminFee(20000));
        $this->assertSame(7500, AppSetting::calculateTopupAdminFee(50000));
        $this->assertSame(6000, AppSetting::calculateTopupAdminFee(200000));

        // Respects SuperAdmin overrides
        AppSetting::set('topup_tier1_fee', '2500');
        $this->assertSame(2500, AppSetting::calculateTopupAdminFee(20000));
    }
}
