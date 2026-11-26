<?php

namespace Tests\Feature;

use App\Livewire\Profile\NotificationSettings;
use App\Models\BalanceTransaction;
use App\Models\Category;
use App\Models\City;
use App\Models\Help;
use App\Models\User;
use App\Notifications\ChatMessageNotification;
use App\Notifications\HelpStatusNotification;
use App\Notifications\NewHelpRequestNotification;
use App\Notifications\TopupApproved;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_table_has_notification_settings_column(): void
    {
        $this->assertTrue(Schema::hasColumn('users', 'notification_settings'));
    }

    public function test_user_can_toggle_all_notification_settings_and_persist_to_database(): void
    {
        $user = User::factory()->create([
            'role' => 'customer',
            'status' => 'active',
            'is_completed' => true,
        ]);

        // Only functional toggles appear on the page
        $this->actingAs($user)
            ->get(route('profile.settings.notifications'))
            ->assertOk()
            ->assertSee('Notifikasi Umum')
            ->assertSee('Suara Notifikasi')
            ->assertSee('Nada Dering Bantuan Urgent')
            ->assertSee('Getar (Vibrate)')
            ->assertSee('Update Transaksi')
            ->assertDontSee('Kirim Tes Notifikasi')
            ->assertDontSee('Pengingat Bantuan')
            ->assertDontSee('Notifikasi Promosi')
            ->assertDontSee('Peringatan Saldo Rendah');

        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'active',
            'is_completed' => true,
        ]);

        Livewire::actingAs($superAdmin)
            ->test(NotificationSettings::class)
            ->assertSee('Kirim Tes Notifikasi');

        Livewire::actingAs($user)
            ->test(NotificationSettings::class)
            ->call('updateSetting', 'soundCall')
            ->assertSet('soundCall', true)
            ->assertDispatched('notification-settings-updated')
            ->assertDispatched('play-preview', setting: 'soundCall')
            ->call('updateSetting', 'sound')
            ->assertSet('sound', false)
            ->call('updateSetting', 'vibrate')
            ->assertSet('vibrate', false)
            ->call('updateSetting', 'transactionUpdate')
            ->assertSet('transactionUpdate', false);

        $user->refresh();
        $this->assertTrue((bool) $user->notification_settings['soundCall']);
        $this->assertFalse((bool) $user->notification_settings['sound']);
        $this->assertFalse((bool) $user->notification_settings['vibrate']);
        $this->assertFalse((bool) $user->notification_settings['transactionUpdate']);
        $this->assertTrue((bool) $user->notification_settings['generalNotification']);
    }

    public function test_notification_via_respects_general_and_transaction_settings(): void
    {
        $city = City::create([
            'name' => 'Kota Yogyakarta',
            'province' => 'DI Yogyakarta',
            'is_active' => true,
        ]);
        $category = Category::create([
            'name' => 'Antar Jemput',
            'icon' => '🚗',
            'is_active' => true,
        ]);
        $user = User::factory()->create([
            'role' => 'customer',
            'status' => 'active',
            'is_completed' => true,
            'notification_settings' => [
                'generalNotification' => false,
                'sound' => true,
                'soundCall' => false,
                'vibrate' => true,
                'transactionUpdate' => false,
            ],
        ]);

        $help = Help::create([
            'user_id' => $user->id,
            'order_id' => 'ORD-TEST-001',
            'help_type' => 'urgent',
            'category_id' => $category->id,
            'city_id' => $city->id,
            'title' => 'Bantuan Urgent',
            'description' => 'Butuh bantuan urgent segera',
            'amount' => 50000,
            'status' => 'menunggu_mitra',
            'location' => 'Jl. Kaliurang',
        ]);

        $tx = BalanceTransaction::create([
            'user_id' => $user->id,
            'type' => 'topup',
            'amount' => 100000,
            'total_payment' => 100000,
            'status' => 'completed',
        ]);

        $this->assertSame([], (new ChatMessageNotification($help->id, 'Halo'))->via($user));
        $this->assertSame([], (new HelpStatusNotification($help, 'menunggu_mitra', 'diambil'))->via($user));
        $this->assertSame([], (new NewHelpRequestNotification($help))->via($user));
        $this->assertSame([], (new TopupApproved($tx))->via($user));

        // Urgent help payload carries help_type for urgent ringtone matching
        $payload = (new NewHelpRequestNotification($help))->toArray($user);
        $this->assertSame('urgent', $payload['help_type']);

        // When re-enabled, channels become ['database']
        $user->update([
            'notification_settings' => [
                'generalNotification' => true,
                'sound' => true,
                'soundCall' => true,
                'vibrate' => true,
                'transactionUpdate' => true,
            ],
        ]);
        $user->refresh();

        $this->assertSame(['database'], (new ChatMessageNotification($help->id, 'Halo'))->via($user));
        $this->assertSame(['database'], (new HelpStatusNotification($help, 'menunggu_mitra', 'diambil'))->via($user));
        $this->assertSame(['database'], (new TopupApproved($tx))->via($user));
    }
}
