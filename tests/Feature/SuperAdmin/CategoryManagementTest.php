<?php

namespace Tests\Feature\SuperAdmin;

use App\Livewire\SuperAdmin\Categories;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_select_icon_and_save_category()
    {
        $superadmin = User::factory()->create([
            'role' => 'super_admin',
            'verified' => true,
            'status' => 'active',
        ]);

        $category = Category::create([
            'name' => 'SPY / Mata - Mata',
            'icon' => '🕵️',
            'description' => 'Layanan investigasi ringan',
            'is_active' => true,
        ]);

        Livewire::actingAs($superadmin)
            ->test(Categories::class)
            ->call('editCategory', $category->id)
            ->assertSet('categoryId', $category->id)
            ->assertSet('name', 'SPY / Mata - Mata')
            ->assertSet('icon', '🕵️')
            ->call('selectIcon', '🛵')
            ->assertSet('icon', '🛵')
            ->call('saveCategory');

        $this->assertEquals('🛵', $category->fresh()->icon);
    }
}
