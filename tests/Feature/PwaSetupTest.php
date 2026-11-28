<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaSetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_manifest_json_and_service_worker_assets_are_valid(): void
    {
        $manifestPath = public_path('manifest.json');
        $swPath = public_path('sw.js');

        $this->assertFileExists($manifestPath);
        $this->assertFileExists($swPath);

        $manifest = json_decode(file_get_contents($manifestPath), true);
        $this->assertIsArray($manifest);
        $this->assertSame('/', $manifest['start_url'] ?? null);
        $this->assertSame('SayaBantu', $manifest['name'] ?? null);
        $this->assertSame('SayaBantu', $manifest['short_name'] ?? null);
        $this->assertSame('standalone', $manifest['display'] ?? null);

        $sizes = array_column($manifest['icons'] ?? [], 'sizes');
        $this->assertContains('192x192', $sizes);
        $this->assertContains('512x512', $sizes);

        $this->assertFileExists(public_path('icons/icon-192x192.png'));
        $this->assertFileExists(public_path('icons/icon-512x512.png'));
        $this->assertFileExists(public_path('icons/apple-touch-icon.png'));

        // Pastikan icon PWA tanpa kotak solid hitam/biru di sudutnya (transparan rapi)
        $iconImg = imagecreatefrompng(public_path('icons/icon-512x512.png'));
        $this->assertNotFalse($iconImg);
        $corner = imagecolorsforindex($iconImg, imagecolorat($iconImg, 0, 0));
        $this->assertSame(127, $corner['alpha']);

        $shortcutUrls = array_column($manifest['shortcuts'] ?? [], 'url');
        $this->assertContains('/bantuan', $shortcutUrls);
        $this->assertNotContains('/helps', $shortcutUrls);

        $swContent = file_get_contents($swPath);
        $this->assertStringContainsString("addEventListener('install'", $swContent);
        $this->assertStringContainsString("addEventListener('fetch'", $swContent);
        $this->assertStringContainsString("url.pathname === '/'", $swContent);
    }

    public function test_public_and_role_pages_include_pwa_manifest_and_service_worker_registration(): void
    {
        $city = City::create([
            'name' => 'Jakarta Selatan',
            'province' => 'DKI Jakarta',
            'is_active' => true,
        ]);

        $customer = User::factory()->create([
            'role' => 'customer',
            'city_id' => $city->id,
            'status' => 'active',
            'verified' => true,
            'is_completed' => true,
            'email_verified_at' => now(),
        ]);

        $mitra = User::factory()->create([
            'role' => 'mitra',
            'city_id' => $city->id,
            'status' => 'active',
            'verified' => true,
            'is_completed' => true,
            'email_verified_at' => now(),
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
            'city_id' => $city->id,
            'status' => 'active',
            'verified' => true,
            'is_completed' => true,
            'email_verified_at' => now(),
        ]);

        $superadmin = User::factory()->create([
            'role' => 'super_admin',
            'city_id' => $city->id,
            'status' => 'active',
            'verified' => true,
            'is_completed' => true,
            'email_verified_at' => now(),
        ]);

        // Landing page & daftar bantuan tamu tidak menampilkan banner install PWA (tetap register manifest & SW)
        foreach (['/', '/bantuan', '/customer/helps'] as $url) {
            $res = $this->get($url);
            $res->assertOk();
            $res->assertSee('rel="manifest" href="/manifest.json"', false);
            $res->assertSee("navigator.serviceWorker.register('/sw.js'", false);
            $res->assertDontSee('id="pwa-install-banner"', false);
        }

        // Halaman login menampilkan banner install PWA
        $loginRes = $this->get('/login');
        $loginRes->assertOk();
        $loginRes->assertSee('rel="manifest" href="/manifest.json"', false);
        $loginRes->assertSee("navigator.serviceWorker.register('/sw.js'", false);
        $loginRes->assertSee('id="pwa-install-banner"', false);
        $loginRes->assertSee('id="sb-pwa-splash"', false);
        $loginRes->assertSee('berhasil ditambahkan ke layar beranda', false);

        // Customer & Mitra mobile-first layouts include install banner + manifest + SW
        $this->actingAs($customer)->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('rel="manifest" href="/manifest.json"', false)
            ->assertSee("navigator.serviceWorker.register('/sw.js'", false)
            ->assertSee('pwa-install-banner', false);

        $this->actingAs($mitra)->get(route('mitra.dashboard'))
            ->assertOk()
            ->assertSee('rel="manifest" href="/manifest.json"', false)
            ->assertSee("navigator.serviceWorker.register('/sw.js'", false)
            ->assertSee('pwa-install-banner', false);

        // Admin & SuperAdmin layouts include manifest + SW
        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('rel="manifest" href="/manifest.json"', false)
            ->assertSee("navigator.serviceWorker.register('/sw.js'", false);

        $this->actingAs($superadmin)->get(route('superadmin.dashboard'))
            ->assertOk()
            ->assertSee('rel="manifest" href="/manifest.json"', false)
            ->assertSee("navigator.serviceWorker.register('/sw.js'", false);
    }
}
