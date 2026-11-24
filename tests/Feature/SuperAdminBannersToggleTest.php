<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SuperAdminBannersToggleTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->create([
            'role' => 'super_admin',
            'status' => 'active',
            'verified' => true,
            'is_completed' => true,
        ]);
    }

    public function test_superadmin_can_toggle_global_promos_enabled(): void
    {
        $admin = $this->superAdmin();

        Livewire::actingAs($admin, 'web')
            ->test(\App\Livewire\SuperAdmin\Banners::class)
            ->assertSet('promosEnabled', true)
            ->call('togglePromosEnabled')
            ->assertSet('promosEnabled', false);

        $this->assertEquals('0', AppSetting::get('dashboard_promos_enabled'));

        // Toggle back to ON
        Livewire::actingAs($admin, 'web')
            ->test(\App\Livewire\SuperAdmin\Banners::class)
            ->call('togglePromosEnabled')
            ->assertSet('promosEnabled', true);

        $this->assertEquals('1', AppSetting::get('dashboard_promos_enabled'));
    }

    public function test_promos_hidden_when_global_toggle_off(): void
    {
        AppSetting::set('dashboard_promos', json_encode([
            [
                'id' => 'promo_special_test',
                'layout' => 'standard',
                'is_active' => true,
                'badge' => 'Promo Diskon',
                'title' => 'Judul Promo Khusus Test',
                'description' => 'Hemat hingga 50%',
                'button_text' => 'Kunjungi',
                'button_link' => '/customer/helps/create',
            ]
        ]));

        // When ON (default)
        AppSetting::set('dashboard_promos_enabled', '1');
        $this->get(route('public.helps'))
            ->assertSuccessful()
            ->assertSee('Judul Promo Khusus Test');

        // When OFF
        AppSetting::set('dashboard_promos_enabled', '0');
        $this->get(route('public.helps'))
            ->assertSuccessful()
            ->assertDontSee('Judul Promo Khusus Test');
    }

    public function test_superadmin_can_toggle_individual_promo_active(): void
    {
        $admin = $this->superAdmin();

        AppSetting::set('dashboard_promos', json_encode([
            [
                'id' => 'promo_test_individual',
                'layout' => 'standard',
                'is_active' => true,
                'badge' => 'Badge Test',
                'title' => 'Banner Individual Test',
                'description' => 'Deskripsi',
                'button_text' => 'Buka',
                'button_link' => '/customer/helps/create',
            ]
        ]));

        Livewire::actingAs($admin, 'web')
            ->test(\App\Livewire\SuperAdmin\Banners::class)
            ->call('togglePromoActive', 'promo_test_individual');

        $promos = json_decode((string) AppSetting::get('dashboard_promos'), true);
        $this->assertFalse($promos[0]['is_active']);
    }
}
