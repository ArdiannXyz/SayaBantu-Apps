<?php

namespace Tests\Feature;

use App\Models\PushSubscription;
use App\Models\User;
use App\Services\WebPushService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PushNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_public_vapid_key(): void
    {
        $response = $this->getJson(route('api.push.key'));

        $response->assertSuccessful()
            ->assertJsonStructure(['success', 'publicKey']);

        $this->assertNotEmpty($response->json('publicKey'));
    }

    public function test_unauthenticated_user_cannot_subscribe_or_test(): void
    {
        $this->postJson(route('api.push.subscribe'), [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-endpoint-123',
            'keys' => [
                'p256dh' => 'test-p256dh-key',
                'auth' => 'test-auth-key',
            ],
        ])->assertUnauthorized();

        $this->postJson(route('api.push.test'))->assertUnauthorized();
    }

    public function test_authenticated_user_can_subscribe_and_unsubscribe(): void
    {
        $user = User::factory()->create();

        $subPayload = [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/device-endpoint-xyz',
            'keys' => [
                'p256dh' => 'mock-p256dh-public-key',
                'auth' => 'mock-auth-secret',
            ],
            'contentEncoding' => 'aesgcm',
        ];

        // 1. Subscribe
        $response = $this->actingAs($user)
            ->postJson(route('api.push.subscribe'), $subPayload);

        $response->assertSuccessful()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/device-endpoint-xyz',
            'endpoint_hash' => hash('sha256', 'https://fcm.googleapis.com/fcm/send/device-endpoint-xyz'),
        ]);

        // 2. Test notification returns true or handles mock delivery
        $testResponse = $this->actingAs($user)
            ->postJson(route('api.push.test'));

        $testResponse->assertSuccessful()
            ->assertJsonStructure(['success', 'message']);

        // 3. Unsubscribe
        $unsubResponse = $this->actingAs($user)
            ->postJson(route('api.push.unsubscribe'), [
                'endpoint' => 'https://fcm.googleapis.com/fcm/send/device-endpoint-xyz',
            ]);

        $unsubResponse->assertSuccessful()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('push_subscriptions', [
            'user_id' => $user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/device-endpoint-xyz',
        ]);
    }

    public function test_web_push_service_handles_missing_subscriptions_gracefully(): void
    {
        $user = User::factory()->create();

        // User without subscriptions should return 0 delivered
        $sentCount = WebPushService::sendToUser(
            $user,
            'Judul Bantuan',
            'Pesan bantuan masuk'
        );

        $this->assertEquals(0, $sentCount);
    }

    public function test_layouts_render_adaptive_push_notification_containers(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'status' => 'active', 'verified' => true, 'is_completed' => true]);
        $mitra = User::factory()->create(['role' => 'mitra', 'status' => 'active', 'verified' => true, 'is_completed' => true]);
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active', 'verified' => true]);
        $superadmin = User::factory()->create(['role' => 'super_admin', 'status' => 'active', 'verified' => true]);

        $this->actingAs($customer)
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('id="customer-global-notification" class="sb-push-dock"', false);

        $this->actingAs($mitra)
            ->get(route('mitra.dashboard'))
            ->assertOk()
            ->assertSee('id="mitra-global-notification" class="sb-push-dock"', false);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('md:bottom-5 md:right-5', false);

        $this->actingAs($superadmin)
            ->get(route('superadmin.dashboard'))
            ->assertOk()
            ->assertSee('md:bottom-5 md:right-5', false);
    }

    public function test_service_worker_always_shows_native_os_notification_on_push(): void
    {
        $swContent = file_get_contents(public_path('sw.js'));

        $this->assertNotFalse($swContent);
        $this->assertStringContainsString('self.registration.showNotification(title, options)', $swContent);
        $this->assertStringNotContainsString('if (!hasFocusedClient)', $swContent);
        $this->assertStringContainsString("sayabantu-v6", $swContent);
    }

    public function test_authenticated_layouts_render_push_optin_banner_and_auth_meta(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'status' => 'active', 'verified' => true, 'is_completed' => true]);
        $mitra = User::factory()->create(['role' => 'mitra', 'status' => 'active', 'verified' => true, 'is_completed' => true]);
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active', 'verified' => true]);
        $superadmin = User::factory()->create(['role' => 'super_admin', 'status' => 'active', 'verified' => true]);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('<meta name="sb-authenticated" content="0">', false)
            ->assertDontSee('id="sb-push-optin-banner"', false);

        foreach ([
            [$customer, 'customer.dashboard'],
            [$mitra, 'mitra.dashboard'],
            [$admin, 'admin.dashboard'],
            [$superadmin, 'superadmin.dashboard'],
        ] as [$actor, $routeName]) {
            $this->actingAs($actor)
                ->get(route($routeName))
                ->assertOk()
                ->assertSee('<meta name="sb-authenticated" content="1">', false)
                ->assertSee('id="sb-push-optin-banner"', false);
        }
    }
}
