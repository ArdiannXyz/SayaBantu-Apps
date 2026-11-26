<?php

namespace Tests\Feature;

use App\Livewire\Admin\TransactionsLog;
use App\Models\BalanceTransaction;
use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminTransactionsLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_wilayah_only_sees_transactions_in_managed_cities(): void
    {
        $yogya = City::create(['name' => 'Kota Yogyakarta', 'province' => 'DI Yogyakarta', 'is_active' => true]);
        $sleman = City::create(['name' => 'Kabupaten Sleman', 'province' => 'DI Yogyakarta', 'is_active' => true]);

        $adminYogya = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'verified' => true,
            'is_completed' => true,
            'city_id' => $yogya->id,
        ]);

        $mitraYogya = User::factory()->create([
            'role' => 'mitra',
            'name' => 'Mitra Yogya',
            'city_id' => $yogya->id,
            'balance' => 90000,
        ]);

        $mitraSleman = User::factory()->create([
            'role' => 'mitra',
            'name' => 'Mitra Sleman',
            'city_id' => $sleman->id,
            'balance' => 150000,
        ]);

        BalanceTransaction::create([
            'user_id' => $mitraYogya->id,
            'amount' => 90000,
            'type' => 'topup',
            'description' => 'Auto-confirm Bantuan #101',
            'status' => 'completed',
            'reference_id' => 101,
        ]);

        BalanceTransaction::create([
            'user_id' => $mitraSleman->id,
            'amount' => 150000,
            'type' => 'topup',
            'description' => 'Auto-confirm Bantuan #202',
            'status' => 'completed',
            'reference_id' => 202,
        ]);

        $this->actingAs($adminYogya)
            ->get(route('admin.transactions.logs'))
            ->assertOk()
            ->assertSee('Log Transaksi Saldo Wilayah')
            ->assertSee('Mitra Yogya')
            ->assertSee('Auto-confirm Bantuan #101')
            ->assertDontSee('Mitra Sleman')
            ->assertDontSee('Auto-confirm Bantuan #202');

        // Spoofing city_id for another city or viewing another city user balance stays blocked
        Livewire::actingAs($adminYogya)
            ->test(TransactionsLog::class)
            ->set('city_id', (string) $sleman->id)
            ->assertSee('Mitra Yogya')
            ->assertDontSee('Mitra Sleman')
            ->call('viewUserBalance', $mitraSleman->id)
            ->assertSet('showUserBalanceModal', false)
            ->call('viewUserBalance', $mitraYogya->id)
            ->assertSet('showUserBalanceModal', true);
    }

    public function test_non_admin_cannot_access_admin_transactions_log(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'status' => 'active',
            'is_completed' => true,
        ]);

        $this->actingAs($customer)
            ->get(route('admin.transactions.logs'))
            ->assertRedirect(route('customer.dashboard'));
    }
}
