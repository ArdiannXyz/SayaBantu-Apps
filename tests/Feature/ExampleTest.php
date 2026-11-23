<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee(route('public.helps'));
    }

    public function test_public_helps_page_returns_successful_response(): void
    {
        \App\Models\AppSetting::set('dashboard_promos', json_encode([
            [
                'id' => 'promo_test_1',
                'layout' => 'standard',
                'is_active' => true,
                'badge' => 'Promo Spesial',
                'title' => 'Diskon Bantuan Rumah Tangga',
                'description' => 'Hemat hingga 50%',
                'button_text' => 'Pesan Sekarang',
                'button_link' => '/customer/helps/create',
            ]
        ]));

        $response = $this->get('/bantuan');

        $response->assertStatus(200);
        $response->assertSee('Daftar Bantuan Tersedia');
        $response->assertSee('Kategori Layanan Tersedia');
        $response->assertSee('Lanjutkan');
        $response->assertSee('text-amber-300');
        $response->assertDontSee('Permintaan Bantuan Terbuka');
        $response->assertDontSee('Belum memiliki akun?');
        $response->assertSee('Promo Spesial');
        $response->assertSee('Diskon Bantuan Rumah Tangga');
        $response->assertSee(route('login'));
        $response->assertSee(route('register'));
    }
}
