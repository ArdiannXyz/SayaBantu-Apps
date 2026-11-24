<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_all_bottom_navbar_routes(): void
    {
        City::create(['name' => 'Kota Test', 'province' => 'DI Yogyakarta', 'is_active' => true]);
        Category::create(['name' => 'Kategori Test', 'is_active' => true]);

        // 1. Beranda / Bantuan publik
        $this->get(route('public.helps'))
            ->assertSuccessful()
            ->assertSee('Daftar')
            ->assertSee('Masuk');

        // 2. Buat Order (mode tamu)
        $this->get(route('customer.helps.create'))
            ->assertSuccessful()
            ->assertSee('Mode Tamu')
            ->assertSee('Daftar & Pesan Bantuan');

        // 3. Pesanan (mode tamu)
        $this->get(route('customer.helps.index'))
            ->assertSuccessful()
            ->assertSee('Mode Tamu')
            ->assertSee('Belum Ada Riwayat Pesanan');

        // 4. Chat (mode tamu)
        $this->get(route('customer.chat'))
            ->assertSuccessful()
            ->assertSee('Mode Tamu')
            ->assertSee('Percakapan Belum Tersedia');

        // 5. Profil (mode tamu)
        $this->get(route('profile'))
            ->assertSuccessful()
            ->assertSee('Pengunjung Tamu')
            ->assertSee('Mode Tamu');
    }

    public function test_guest_order_creation_redirects_to_register_with_draft_saved(): void
    {
        $city = City::create(['name' => 'Kota Test', 'province' => 'DI Yogyakarta', 'is_active' => true]);
        $category = Category::create(['name' => 'Kategori Test', 'is_active' => true]);

        \Livewire\Livewire::test(\App\Livewire\Customer\Helps\Create::class)
            ->set('title', 'Bantu Angkut Lemari')
            ->set('description', 'Tolong bantu pindahkan lemari ke lantai 2 kamar kost.')
            ->set('category_id', (string) $category->id)
            ->set('amount', 50000)
            ->set('city_id', (string) $city->id)
            ->set('location', 'Jl. Kaliurang KM 5')
            ->set('full_address', 'Kos Flamboyan No. 12')
            ->call('prepareConfirm')
            ->assertRedirect(route('register'));

        $this->assertTrue(session()->has('pending_help_order'));
        $this->assertEquals('Bantu Angkut Lemari', session('pending_help_order')['title']);
    }

    public function test_login_back_button_points_to_public_helps(): void
    {
        $response = $this->get(route('login'));
        $response->assertSuccessful();
        $response->assertSee(route('public.helps'));
    }
}
