<?php

namespace Tests\Feature;

use App\Enums\HelpStatus;
use App\Models\BalanceTransaction;
use App\Models\Category;
use App\Models\City;
use App\Models\Help;
use App\Models\User;
use App\Models\UserBalance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrencyDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_dashboard_and_history_display_millions_as_jt(): void
    {
        $city = City::create(['name' => 'Kota Bantul', 'province' => 'DI Yogyakarta', 'is_active' => true]);
        $category = Category::create(['name' => 'Perbaikan', 'is_active' => true]);

        $customer = User::factory()->create([
            'role' => 'customer',
            'status' => 'active',
            'verified' => true,
            'is_completed' => true,
            'email_verified_at' => now(),
            'city_id' => $city->id,
        ]);

        UserBalance::create(['user_id' => $customer->id, 'balance' => 1500000]);

        Help::create([
            'user_id' => $customer->id,
            'city_id' => $city->id,
            'category_id' => $category->id,
            'help_type' => 'scheduled',
            'title' => 'Perbaikan Jutaan',
            'description' => 'Order bernilai satu juta',
            'amount' => 1000000,
            'status' => HelpStatus::MENUNGGU_MITRA->value,
        ]);

        $this->actingAs($customer)
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('Rp 1,5 jt')
            ->assertSee('Rp 1 jt')
            ->assertDontSee('Rp 1.500.000')
            ->assertDontSee('Rp 1.000.000');
    }

    public function test_mitra_dashboard_and_admin_topup_display_millions_as_jt(): void
    {
        $city = City::create(['name' => 'Kota Yogyakarta', 'province' => 'DI Yogyakarta', 'is_active' => true]);

        $mitra = User::factory()->create([
            'role' => 'mitra',
            'status' => 'active',
            'verified' => true,
            'is_completed' => true,
            'email_verified_at' => now(),
            'city_id' => $city->id,
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'verified' => true,
            'email_verified_at' => now(),
            'city_id' => $city->id,
        ]);

        UserBalance::create(['user_id' => $mitra->id, 'balance' => 2000000]);

        BalanceTransaction::create([
            'user_id' => $mitra->id,
            'amount' => 1000000,
            'admin_fee' => 5000,
            'total_payment' => 1005000,
            'type' => 'topup',
            'status' => 'waiting_approval',
            'request_code' => 'TOPUP-JT-001',
            'payment_method' => 'bank_bca',
        ]);

        $this->actingAs($mitra)
            ->get(route('mitra.dashboard'))
            ->assertOk()
            ->assertSee('Rp 2 jt')
            ->assertDontSee('Rp 2.000.000');

        $this->actingAs($admin)
            ->get(route('admin.topup.approvals'))
            ->assertOk()
            ->assertSee('Rp 1 jt')
            ->assertSee('Rp 1.005.000')
            ->assertDontSee('Rp 1.000.000')
            ->assertDontSee('Rp 1,01 jt');
    }
}
