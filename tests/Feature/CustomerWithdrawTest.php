<?php

namespace Tests\Feature;

use App\Livewire\SuperAdmin\Settings\TarifRadius;
use App\Models\AppSetting;
use App\Models\BalanceTransaction;
use App\Models\City;
use App\Models\User;
use App\Models\UserBankAccount;
use App\Models\WithdrawRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerWithdrawTest extends TestCase
{
    use RefreshDatabase;

    private function createCustomer(array $overrides = []): User
    {
        $city = City::create([
            'name' => 'Jakarta Selatan',
            'province' => 'DKI Jakarta',
            'is_active' => true,
        ]);

        return User::factory()->create(array_merge([
            'role' => 'customer',
            'city_id' => $city->id,
            'status' => 'active',
            'verified' => false,
            'is_completed' => true,
            'email_verified_at' => now(),
            'phone' => '081234567890',
            'address' => 'Jl. Sudirman No. 1',
            'transaction_pin' => Hash::make('123456'),
        ], $overrides));
    }

    private function seedBalanceAndBank(User $user, int $balance = 150000): UserBankAccount
    {
        BalanceTransaction::create([
            'user_id' => $user->id,
            'amount' => $balance,
            'type' => 'topup',
            'status' => 'completed',
            'description' => 'Top Up QRIS',
            'processed_at' => now(),
        ]);

        return UserBankAccount::create([
            'user_id' => $user->id,
            'bank_code' => 'BCA',
            'bank_name' => 'Bank Central Asia',
            'account_number' => '1234567890',
            'account_name' => $user->name,
            'is_primary' => true,
        ]);
    }

    public function test_unverified_and_verified_customers_can_access_and_submit_withdraw(): void
    {
        foreach ([false, true] as $isVerified) {
            $customer = $this->createCustomer([
                'verified' => $isVerified,
                'phone' => $isVerified ? '081299990001' : '081299990002',
            ]);
            $bank = $this->seedBalanceAndBank($customer, 150000);

            $this->actingAs($customer)
                ->get(route('customer.withdraw.form'))
                ->assertOk()
                ->assertSee('Lanjutkan Penarikan');

            $this->actingAs($customer)
                ->post(route('customer.withdraw.request'), [
                    'amount' => '50.000',
                    'bank_account_id' => $bank->id,
                    'pin' => '123456',
                ])
                ->assertRedirect(route('customer.withdraw.form'));

            $withdraw = WithdrawRequest::where('user_id', $customer->id)->first();
            $this->assertNotNull($withdraw);
            $this->assertSame(50000, $withdraw->amount);
            $this->assertSame(WithdrawRequest::STATUS_PENDING, $withdraw->status);
            $this->assertSame(100000, $customer->fresh()->balance);

            $this->assertDatabaseHas('balance_transactions', [
                'user_id' => $customer->id,
                'type' => 'deduction',
                'reference_id' => 'WD-'.$withdraw->id,
                'amount' => 50000,
            ]);
        }
    }

    public function test_customer_with_incomplete_biodata_is_redirected_to_profile_edit(): void
    {
        $customer = $this->createCustomer([
            'phone' => null,
            'address' => '',
        ]);

        $this->actingAs($customer)
            ->get(route('customer.withdraw.form'))
            ->assertRedirect(route('profile.edit'));
    }

    public function test_customer_can_cancel_own_pending_withdraw_and_refund_balance(): void
    {
        $customer = $this->createCustomer(['verified' => false]);
        $bank = $this->seedBalanceAndBank($customer, 120000);

        $this->actingAs($customer)
            ->post(route('customer.withdraw.request'), [
                'amount' => '40000',
                'bank_account_id' => $bank->id,
                'pin' => '123456',
            ]);

        $withdraw = WithdrawRequest::where('user_id', $customer->id)->firstOrFail();
        $this->assertSame(80000, $customer->fresh()->balance);

        $otherCustomer = $this->createCustomer(['phone' => '081288887777']);
        $this->actingAs($otherCustomer)
            ->post(route('customer.withdraw.cancel', $withdraw))
            ->assertForbidden();

        $this->actingAs($customer)
            ->post(route('customer.withdraw.cancel', $withdraw))
            ->assertRedirect(route('customer.withdraw.form', ['force' => 1]));

        $this->assertSame(WithdrawRequest::STATUS_FAILED, $withdraw->fresh()->status);
        $this->assertSame('Dibatalkan oleh pengguna', $withdraw->fresh()->description);
        $this->assertSame(120000, $customer->fresh()->balance);
    }

    public function test_customer_withdraw_history_and_profile_show_customer_copy_and_links(): void
    {
        $customer = $this->createCustomer(['verified' => false]);
        $bank = $this->seedBalanceAndBank($customer, 100000);

        $withdraw = WithdrawRequest::create([
            'user_id' => $customer->id,
            'amount' => 30000,
            'bank_code' => $bank->bank_code,
            'account_number' => $bank->account_number,
            'account_name' => $bank->account_name,
            'status' => WithdrawRequest::STATUS_SUCCESS,
            'processed_at' => now(),
        ]);

        $this->actingAs($customer)
            ->get(route('customer.withdraw.history'))
            ->assertOk()
            ->assertSee('Top Up Saldo')
            ->assertSee('Berhasil')
            ->assertSee(route('customer.withdraw.success', $withdraw->id))
            ->assertDontSee('Pekerjaan Jasa');

        $this->actingAs($customer)
            ->get(route('profile'))
            ->assertOk()
            ->assertSee(route('customer.withdraw.form'))
            ->assertSee(route('customer.bank-accounts'));
    }

    public function test_default_limit_is_3x_per_24h_for_customer_and_mitra_and_configurable_by_superadmin(): void
    {
        foreach (['customer', 'mitra'] as $role) {
            $user = $this->createCustomer([
                'role' => $role,
                'verified' => true,
                'phone' => $role === 'customer' ? '081277770001' : '081277770002',
                'nik' => $role === 'mitra' ? '3174010101010001' : null,
            ]);
            $bank = $this->seedBalanceAndBank($user, 200000);

            // 3 penarikan berhasil dalam 24 jam tetap diperbolehkan
            for ($i = 1; $i <= 3; $i++) {
                WithdrawRequest::create([
                    'user_id' => $user->id,
                    'amount' => 15000,
                    'bank_code' => $bank->bank_code,
                    'account_number' => $bank->account_number,
                    'account_name' => $bank->account_name,
                    'status' => WithdrawRequest::STATUS_SUCCESS,
                    'processed_at' => now()->subMinutes(10),
                ]);
            }

            // Pengajuan ke-4 ditolak karena batas default 3x dalam 24 jam
            $routePrefix = $role === 'customer' ? 'customer' : 'mitra';
            $this->actingAs($user)
                ->post(route($routePrefix.'.withdraw.request'), [
                    'amount' => '15000',
                    'bank_account_id' => $bank->id,
                    'pin' => '123456',
                ])
                ->assertSessionHasErrors('general');

            $this->assertSame(3, WithdrawRequest::where('user_id', $user->id)->count());
        }

        // Super Admin ubah batas menjadi 4x per 24 jam & minimal Rp 20.000
        $superAdmin = $this->createCustomer([
            'role' => 'super_admin',
            'verified' => true,
            'phone' => '081277770099',
        ]);

        Livewire::actingAs($superAdmin)
            ->test(TarifRadius::class)
            ->set('min_withdraw_nominal', 20000)
            ->set('max_withdraw_per_24h', 4)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('20000', AppSetting::get('min_withdraw_nominal'));
        $this->assertSame('4', AppSetting::get('max_withdraw_per_24h'));

        $customer = User::where('phone', '081277770001')->firstOrFail();
        $bankId = $customer->bankAccounts()->firstOrFail()->id;

        // Di bawah minimal baru (15000 < 20000) ditolak
        $this->actingAs($customer)
            ->post(route('customer.withdraw.request'), [
                'amount' => '15000',
                'bank_account_id' => $bankId,
                'pin' => '123456',
            ])
            ->assertSessionHasErrors('amount');

        // Minimal baru (20000) dan kuota ke-4 (dari 4x) sekarang lolos
        $this->actingAs($customer)
            ->post(route('customer.withdraw.request'), [
                'amount' => '20000',
                'bank_account_id' => $bankId,
                'pin' => '123456',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('customer.withdraw.form'));

        $this->assertSame(4, WithdrawRequest::where('user_id', $customer->id)->count());
    }

    public function test_superadmin_settings_forms_render_save_confirmation_prompts(): void
    {
        $superAdmin = $this->createCustomer([
            'role' => 'super_admin',
            'verified' => true,
            'phone' => '081277770098',
        ]);

        $this->actingAs($superAdmin)
            ->get(route('superadmin.pengaturan.bantuan.tarif-radius'))
            ->assertOk()
            ->assertSee('wire:confirm="Apakah Anda yakin ingin menyimpan dan menerapkan perubahan tarif, radius, dan batas penarikan ini?', false);

        $this->actingAs($superAdmin)
            ->get(route('superadmin.pengaturan.bantuan.fee-pendapatan'))
            ->assertOk()
            ->assertSee('wire:confirm="Apakah Anda yakin ingin menyimpan perubahan pengaturan fee & pendapatan platform ini?', false);

        $this->actingAs($superAdmin)
            ->get(route('superadmin.pengaturan.bantuan.biaya-topup'))
            ->assertOk()
            ->assertSee('wire:confirm="Apakah Anda yakin ingin menyimpan perubahan pengaturan biaya admin top-up ini?', false);

        $this->actingAs($superAdmin)
            ->get(route('superadmin.settings.help'))
            ->assertOk()
            ->assertSee('wire:confirm="Apakah Anda yakin ingin', false);
    }
}
