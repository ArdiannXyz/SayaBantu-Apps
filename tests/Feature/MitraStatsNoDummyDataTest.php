<?php

namespace Tests\Feature;

use App\Livewire\Home\Index as HomeIndex;
use App\Livewire\Mitra\Dashboard;
use App\Livewire\Mitra\Profile;
use App\Models\BalanceTransaction;
use App\Models\Category;
use App\Models\City;
use App\Models\Help;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MitraStatsNoDummyDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_mitra_sees_zero_stats_instead_of_dummy_values(): void
    {
        $city = City::create(['name' => 'Yogyakarta', 'province' => 'DIY', 'type' => 'kota']);

        $mitra = User::factory()->create([
            'name' => 'Mitra Baru',
            'role' => 'mitra',
            'status' => 'active',
            'is_completed' => true,
            'city_id' => $city->id,
            'latitude' => -7.7956,
            'longitude' => 110.3695,
        ]);

        Livewire::actingAs($mitra)
            ->test(Dashboard::class)
            ->assertViewHas('totalEarnings', 0.0)
            ->assertViewHas('ratingAvg', '0.0')
            ->assertSee('0.0')
            ->assertSee('Rp 0')
            ->assertDontSee('Rp 1,25 jt')
            ->assertDontSee('4.8')
            ->assertDontSee('Pesanan Sedang Berjalan')
            ->assertDontSee('Budi Santoso');

        $this->actingAs($mitra)
            ->get(route('mitra.withdraw.history'))
            ->assertOk()
            ->assertSee('Belum Ada Transaksi')
            ->assertSee('Rp 0')
            ->assertDontSee('Order #SRB0012')
            ->assertDontSee('Order #PDN0008')
            ->assertDontSee('Order #TBG0005')
            ->assertDontSee('****1234')
            ->assertDontSee('250.000');

        Livewire::actingAs($mitra)
            ->test(Profile::class)
            ->assertSee('0.0')
            ->assertSee('(0 ulasan)')
            ->assertDontSee('4.8')
            ->assertDontSee('(12 ulasan)');
    }

    public function test_mitra_dashboard_and_profile_show_real_earnings_and_rating_and_exclude_topups(): void
    {
        $city = City::create(['name' => 'Yogyakarta', 'province' => 'DIY', 'type' => 'kota']);
        $category = Category::create(['name' => 'Kebersihan', 'slug' => 'kebersihan']);

        $customer = User::factory()->create([
            'name' => 'Customer Satu',
            'role' => 'customer',
            'status' => 'active',
            'is_completed' => true,
            'city_id' => $city->id,
        ]);

        $mitra = User::factory()->create([
            'name' => 'Mitra Berpengalaman',
            'role' => 'mitra',
            'status' => 'active',
            'is_completed' => true,
            'city_id' => $city->id,
            'latitude' => -7.7956,
            'longitude' => 110.3695,
        ]);

        // Wallet top-up should NOT count toward Penghasilan
        BalanceTransaction::create([
            'user_id' => $mitra->id,
            'amount' => 500000,
            'type' => 'topup',
            'description' => 'Top Up Saldo via QRIS',
            'status' => 'completed',
        ]);

        // Actual completed help income SHOULD count toward Penghasilan
        $help = Help::create([
            'user_id' => $customer->id,
            'mitra_id' => $mitra->id,
            'city_id' => $city->id,
            'category_id' => $category->id,
            'title' => 'Bantuan Bersih Rumah',
            'description' => 'Bersih ruang tamu',
            'amount' => 100000,
            'status' => 'selesai',
            'location' => 'Jl. Malioboro',
            'latitude' => -7.7956,
            'longitude' => 110.3695,
        ]);

        BalanceTransaction::create([
            'user_id' => $mitra->id,
            'amount' => 90000,
            'type' => 'topup',
            'description' => "Pendapatan Bantuan #{$help->id}",
            'reference_id' => $help->id,
            'status' => 'completed',
        ]);

        Rating::create([
            'help_id' => $help->id,
            'user_id' => $customer->id,
            'mitra_id' => $mitra->id,
            'rater_id' => $customer->id,
            'ratee_id' => $mitra->id,
            'type' => 'customer_to_mitra',
            'rating' => 5,
            'review' => 'Sangat rapi',
        ]);

        Livewire::actingAs($mitra)
            ->test(Dashboard::class)
            ->assertViewHas('totalEarnings', 90000.0)
            ->assertViewHas('ratingAvg', '5.0')
            ->assertSee('5.0')
            ->assertSee('Rp 90.000');

        Livewire::actingAs($mitra)
            ->test(Profile::class)
            ->assertSee('5.0')
            ->assertSee('(1 ulasan)');
    }

    public function test_home_index_does_not_display_order_terakhir_section(): void
    {
        $city = City::create(['name' => 'Yogyakarta', 'province' => 'DIY', 'type' => 'kota']);
        $category = Category::create(['name' => 'Pindahan', 'slug' => 'pindahan']);

        $customer = User::factory()->create([
            'name' => 'Customer Privat',
            'role' => 'customer',
            'status' => 'active',
            'is_completed' => true,
            'city_id' => $city->id,
        ]);

        Help::create([
            'user_id' => $customer->id,
            'city_id' => $city->id,
            'category_id' => $category->id,
            'title' => 'Order Rahasia Lainnya',
            'description' => 'Tidak boleh tampil di home',
            'amount' => 50000,
            'status' => 'menunggu_mitra',
            'location' => 'Jl. Sudirman',
        ]);

        Livewire::test(HomeIndex::class)
            ->assertDontSee('Order terakhir')
            ->assertDontSee('Order Rahasia Lainnya');

        $this->get(route('public.helps'))
            ->assertOk()
            ->assertDontSee('Order terakhir')
            ->assertDontSee('Order Rahasia Lainnya');

        $newCustomer = User::factory()->create([
            'name' => 'Customer Baru Bersih',
            'role' => 'customer',
            'status' => 'active',
            'is_completed' => true,
            'city_id' => $city->id,
        ]);

        $this->actingAs($newCustomer)
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('Belum Ada Permintaan Aktif')
            ->assertDontSee('Order Rahasia Lainnya');
    }
}
