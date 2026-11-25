<?php

namespace Tests\Feature;

use App\Livewire\SuperAdmin\AdminUsers;
use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SuperAdminAdminUsersCitySelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_toggle_select_all_managed_cities_excluding_other_admins_cities(): void
    {
        $cityA = City::create(['name' => 'Kota Bantul', 'province' => 'DI Yogyakarta', 'is_active' => true]);
        $cityB = City::create(['name' => 'Kota Sleman', 'province' => 'DI Yogyakarta', 'is_active' => true]);
        $cityTaken = City::create(['name' => 'Kota Bekasi', 'province' => 'Jawa Barat', 'is_active' => true]);

        $superadmin = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'active',
            'verified' => true,
            'is_completed' => true,
            'email_verified_at' => now(),
        ]);

        $otherAdmin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'verified' => true,
            'city_id' => $cityTaken->id,
        ]);
        $otherAdmin->managedCities()->sync([$cityTaken->id]);

        $component = Livewire::actingAs($superadmin)
            ->test(AdminUsers::class)
            ->call('openCreateModal')
            ->assertSee('Pilih Semua')
            ->call('toggleSelectAllManagedCities')
            ->assertSet('managed_city_ids', [$cityA->id, $cityB->id])
            ->assertSee('Batal Pilih Semua');

        $component
            ->call('toggleSelectAllManagedCities')
            ->assertSet('managed_city_ids', [])
            ->assertSee('Pilih Semua');

        // Saat memfilter sebagian kota (misal hanya Sleman), pilih semua hanya memilih yang terlihat
        $component
            ->call('toggleSelectAllManagedCities', [$cityB->id, $cityTaken->id])
            ->assertSet('managed_city_ids', [$cityB->id]);
    }
}
