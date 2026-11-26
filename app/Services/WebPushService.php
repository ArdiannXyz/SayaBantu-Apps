<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushService
{
    /**
     * Pastikan konfigurasi OpenSSL berjalan mulus di Windows
     */
    protected static function ensureOpenSslEnvironment(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $conf = config('webpush.openssl_conf', 'C:/laragon/bin/php/php-8.3/extras/ssl/openssl.cnf');
            if ($conf && file_exists($conf)) {
                putenv("OPENSSL_CONF={$conf}");
                $_ENV['OPENSSL_CONF'] = $conf;
                $_SERVER['OPENSSL_CONF'] = $conf;
            }
        }
    }

    /**
     * Dapatkan instance WebPush client terkonfigurasi VAPID
     */
    public static function getWebPushClient(): WebPush
    {
        self::ensureOpenSslEnvironment();

        $auth = [
            'VAPID' => [
                'subject' => config('webpush.vapid.subject', 'mailto:admin@sayabantu.com'),
                'publicKey' => config('webpush.vapid.public_key'),
                'privateKey' => config('webpush.vapid.private_key'),
            ],
        ];

        return new WebPush($auth);
    }

    /**
     * Simpan atau perbarui subscription browser pengguna
     */
    public static function subscribe(User $user, array $data, ?string $userAgent = null): PushSubscription
    {
        $endpoint = (string) ($data['endpoint'] ?? '');
        if (empty($endpoint)) {
            throw new \InvalidArgumentException('Push endpoint is required.');
        }

        $keys = $data['keys'] ?? [];
        $publicKey = $keys['p256dh'] ?? ($data['publicKey'] ?? ($data['public_key'] ?? null));
        $authToken = $keys['auth'] ?? ($data['authToken'] ?? ($data['auth_token'] ?? null));
        $encoding = $data['contentEncoding'] ?? ($data['content_encoding'] ?? 'aesgcm');

        $endpointHash = hash('sha256', $endpoint);

        return PushSubscription::updateOrCreate(
            [
                'user_id' => $user->id,
                'endpoint_hash' => $endpointHash,
            ],
            [
                'endpoint' => $endpoint,
                'public_key' => $publicKey,
                'auth_token' => $authToken,
                'content_encoding' => $encoding,
                'user_agent' => $userAgent ? substr($userAgent, 0, 500) : null,
            ]
        );
    }

    /**
     * Hapus subscription browser tertentu
     */
    public static function unsubscribe(User $user, string $endpoint): bool
    {
        $endpointHash = hash('sha256', $endpoint);

        return (bool) PushSubscription::where('user_id', $user->id)
            ->where('endpoint_hash', $endpointHash)
            ->delete();
    }

    /**
     * Kirim push notification ke user tertentu
     */
    public static function sendToUser(
        User $user,
        string $title,
        string $body,
        ?string $url = null,
        ?string $icon = null,
        ?string $tag = null,
        array $extraData = []
    ): int {
        // Cek pengaturan notifikasi pengguna
        $settings = $user->getNotificationSettings();
        if (! $settings['generalNotification']) {
            return 0; // Pengguna menonaktifkan notifikasi umum
        }

        $subscriptions = $user->pushSubscriptions()->get();
        if ($subscriptions->isEmpty()) {
            return 0;
        }

        $isUrgent = (bool) ($extraData['isUrgent'] ?? false);
        $vibratePattern = $settings['vibrate']
            ? ($isUrgent ? [300, 100, 300, 100, 300] : [200, 100, 200])
            : [];

        $payload = [
            'title' => $title,
            'body' => $body,
            'icon' => $icon ?: asset('icons/icon-192x192.png'),
            'badge' => asset('icons/icon-192x192.png'),
            'url' => $url ?: url('/'),
            'tag' => $tag ?: ('sayabantu-' . time()),
            'vibrate' => $vibratePattern,
            'requireInteraction' => $isUrgent,
            'data' => array_merge([
                'url' => $url ?: url('/'),
                'time' => time(),
                'isUrgent' => $isUrgent,
                'sound' => $settings['sound'],
                'soundCall' => $settings['soundCall'],
                'vibrate' => $settings['vibrate'],
            ], $extraData),
        ];

        return self::sendToSubscriptions($subscriptions, $payload);
    }

    /**
     * Kirim push notification ke koleksi subscriptions
     */
    public static function sendToSubscriptions($subscriptions, array $payload): int
    {
        if (empty($subscriptions) || count($subscriptions) === 0) {
            return 0;
        }

        try {
            $webPush = self::getWebPushClient();
            $payloadJson = json_encode($payload);

            foreach ($subscriptions as $subModel) {
                $sub = Subscription::create([
                    'endpoint' => $subModel->endpoint,
                    'publicKey' => $subModel->public_key,
                    'authToken' => $subModel->auth_token,
                    'contentEncoding' => $subModel->content_encoding ?: 'aesgcm',
                ]);

                $webPush->queueNotification($sub, $payloadJson);
            }

            $successCount = 0;
            foreach ($webPush->flush() as $report) {
                $endpoint = (string) $report->getRequest()->getUri();
                if ($report->isSuccess()) {
                    $successCount++;
                } else {
                    Log::warning("[WebPush] Pengiriman notifikasi gagal ({$report->getReason()}) ke endpoint: " . substr($endpoint, 0, 80));

                    // Hapus subscription jika token/endpoint sudah expired atau tidak valid (HTTP 404 / 410)
                    if ($report->isSubscriptionExpired()) {
                        PushSubscription::where('endpoint_hash', hash('sha256', $endpoint))->delete();
                        Log::info("[WebPush] Menghapus subscription expired: " . substr($endpoint, 0, 80));
                    }
                }
            }

            return $successCount;
        } catch (\Throwable $e) {
            Log::error('[WebPush] Error saat mengirim web push notification: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return 0;
        }
    }

    /**
     * Kirim notifikasi uji coba ke pengguna
     */
    public static function sendTestNotification(User $user): bool
    {
        $sentCount = self::sendToUser(
            $user,
            '🔔 Tes Push Notifikasi SayaBantu',
            'Push notifikasi berhasil aktif di perangkat Anda! Anda akan menerima update pesanan dan pesan secara langsung.',
            url('/profile'),
            null,
            'sayabantu-test-' . $user->id
        );

        return $sentCount > 0;
    }
}
