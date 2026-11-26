<?php

namespace Tests\Feature;

use App\Livewire\Customer\Helps\Create as CustomerHelpsCreate;
use App\Livewire\Mitra\AllHelps;
use App\Livewire\Mitra\Dashboard as MitraDashboard;
use App\Models\Category;
use App\Models\City;
use App\Models\Help;
use App\Models\User;
use App\Models\UserBalance;
use App\Notifications\NewHelpRequestNotification;
use App\Services\NotificationUrlResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class MitraRadiusAndNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_table_has_coordinate_columns_and_mitra_dashboard_syncs_gps(): void
    {
        $this->assertTrue(Schema::hasColumn('users', 'latitude'));
        $this->assertTrue(Schema::hasColumn('users', 'longitude'));

        $city = City::create([
            'name' => 'Kota Yogyakarta',
            'province' => 'DI Yogyakarta',
            'latitude' => -7.7972,
            'longitude' => 110.3688,
            'is_active' => true,
        ]);

        $mitra = User::factory()->create([
            'role' => 'mitra',
            'status' => 'active',
            'verified' => true,
            'is_completed' => true,
            'city_id' => $city->id,
        ]);

        Livewire::actingAs($mitra)
            ->test(MitraDashboard::class)
            ->call('setCoordinates', -7.7512345, 110.3712345)
            ->assertSet('gpsReady', true);

        $mitra->refresh();
        $this->assertEqualsWithDelta(-7.7512345, (float) $mitra->latitude, 0.0001);
        $this->assertEqualsWithDelta(110.3712345, (float) $mitra->longitude, 0.0001);
    }

    public function test_cross_city_notification_and_visibility_within_10km_radius(): void
    {
        Notification::fake();

        $yogya = City::create([
            'name' => 'Kota Yogyakarta',
            'province' => 'DI Yogyakarta',
            'latitude' => -7.7972,
            'longitude' => 110.3688,
            'is_active' => true,
        ]);

        $sleman = City::create([
            'name' => 'Kabupaten Sleman',
            'province' => 'DI Yogyakarta',
            'latitude' => -7.7127,
            'longitude' => 110.3552,
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Antar Barang',
            'icon' => '📦',
            'is_active' => true,
        ]);

        $customer = User::factory()->create([
            'role' => 'customer',
            'status' => 'active',
            'is_completed' => true,
            'verified' => false,
            'city_id' => $yogya->id,
            'phone' => '081211112222',
            'address' => 'Jl. Kaliurang KM 6',
        ]);
        UserBalance::create(['user_id' => $customer->id, 'balance' => 500000]);

        // Mitra in Sleman (neighboring city), GPS ~2.2 km from order in north Yogya
        $mitraNearbyCrossCity = User::factory()->create([
            'role' => 'mitra',
            'status' => 'active',
            'verified' => true,
            'is_completed' => true,
            'is_shadow_banned' => false,
            'city_id' => $sleman->id,
            'latitude' => -7.7127,
            'longitude' => 110.3552,
            'nik' => '3404091111111111',
            'ktp_photo' => 'ktp-photos/m1.jpg',
            'selfie_photo' => 'selfie-photos/m1.jpg',
            'phone' => '081233334444',
            'address' => 'Sleman',
        ]);

        // Mitra in Yogya (same city), GPS ~24 km south (beyond 10km)
        $mitraFarSameCity = User::factory()->create([
            'role' => 'mitra',
            'status' => 'active',
            'verified' => true,
            'is_completed' => true,
            'is_shadow_banned' => false,
            'city_id' => $yogya->id,
            'latitude' => -7.9500,
            'longitude' => 110.3688,
            'nik' => '3404092222222222',
            'ktp_photo' => 'ktp-photos/m2.jpg',
            'selfie_photo' => 'selfie-photos/m2.jpg',
            'phone' => '081255556666',
            'address' => 'Yogya Selatan',
        ]);

        // Order placed in Kota Yogyakarta at -7.7300, 110.3650 (~2.2 km from Sleman mitra, ~24.5 km from south Yogya mitra)
        Livewire::actingAs($customer)
            ->test(CustomerHelpsCreate::class)
            ->set('title', 'Ambil Dokumen')
            ->set('category_id', (string) $category->id)
            ->set('amount', '50000')
            ->set('city_id', (string) $yogya->id)
            ->set('location', 'Ringroad Utara')
            ->set('full_address', 'Jl. Ringroad Utara No. 1')
            ->set('latitude', -7.7300)
            ->set('longitude', 110.3650)
            ->set('help_type', 'urgent')
            ->set('auto_cancel_minutes', 60)
            ->set('scheduled_date', now()->format('Y-m-d'))
            ->set('scheduled_time', now()->addHour()->format('H:i'))
            ->set('description', 'Tolong ambilkan dokumen di Ringroad Utara')
            ->set('equipment_provided', 'sender')
            ->call('save')
            ->assertHasNoErrors();

        $help = Help::where('user_id', $customer->id)->firstOrFail();

        // Cross-city nearby mitra gets notification; out-of-radius same-city mitra does not
        Notification::assertSentTo($mitraNearbyCrossCity, NewHelpRequestNotification::class);
        Notification::assertNotSentTo($mitraFarSameCity, NewHelpRequestNotification::class);

        // Notification URL for unclaimed help_request points to available orders list, never 403 HelpDetail
        $resolvedUrl = NotificationUrlResolver::resolveUrl([
            'type' => 'help_request',
            'help_id' => $help->id,
        ], $mitraNearbyCrossCity);
        $this->assertSame(route('mitra.helps.all', ['tab' => 'tersedia']), $resolvedUrl);

        // Direct hit to /mitra/helps/{id} while waiting_mitra redirects cleanly instead of 403
        $this->actingAs($mitraNearbyCrossCity)
            ->get(route('mitra.helps.detail', $help->id))
            ->assertRedirect(route('mitra.helps.all', ['tab' => 'tersedia']));

        // Nearby cross-city mitra sees order on Dashboard and AllHelps, and can take it
        Livewire::actingAs($mitraNearbyCrossCity)
            ->test(MitraDashboard::class)
            ->assertSee('Ambil Dokumen');

        Livewire::actingAs($mitraNearbyCrossCity)
            ->test(AllHelps::class)
            ->set('tab', 'tersedia')
            ->assertSee('Ambil Dokumen')
            ->call('takeHelp', $help->id, -7.7127, 110.3552);

        $help->refresh();
        $this->assertSame($mitraNearbyCrossCity->id, $help->mitra_id);

        // Once taken, notification URL resolves directly to detail page for assigned mitra
        $resolvedAfterTake = NotificationUrlResolver::resolveUrl([
            'type' => 'help_request',
            'help_id' => $help->id,
        ], $mitraNearbyCrossCity);
        $this->assertSame(route('mitra.helps.detail', $help->id), $resolvedAfterTake);

        // Another mitra who does NOT own the already-taken help still gets 403
        $this->actingAs($mitraFarSameCity)
            ->get(route('mitra.helps.detail', $help->id))
            ->assertForbidden();

        // Assigned mitra opening completion modal sees direct camera controls for before & completion photo
        $help->update(['status' => 'in_progress']);
        Livewire::actingAs($mitraNearbyCrossCity)
            ->test(\App\Livewire\Mitra\Helps\HelpDetail::class, ['id' => $help->id])
            ->set('showCompletionModal', true)
            ->assertSee("openDirectCamera('before_photo_input', 'before')", false)
            ->assertSee("openDirectCamera('completion_photo_input', 'after')", false)
            ->assertSee('completion_photo_gallery', false);
    }
}
