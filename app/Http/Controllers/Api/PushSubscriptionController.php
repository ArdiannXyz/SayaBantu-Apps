<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WebPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    /**
     * Dapatkan Public VAPID Key untuk client-side registration
     */
    public function getPublicKey(): JsonResponse
    {
        $publicKey = config('webpush.vapid.public_key') ?: env('VAPID_PUBLIC_KEY');

        if (empty($publicKey)) {
            return response()->json([
                'success' => false,
                'publicKey' => null,
                'message' => 'VAPID Public Key belum dikonfigurasi di server.',
            ], 503);
        }

        return response()->json([
            'success' => true,
            'publicKey' => $publicKey,
        ]);
    }

    /**
     * Simpan langganan (subscription) Web Push perangkat pengguna
     */
    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => 'required|string',
            'keys' => 'required|array',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
        ]);

        $user = $request->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Autentikasi diperlukan.',
            ], 401);
        }

        try {
            $subscription = WebPushService::subscribe(
                $user,
                $request->all(),
                $request->header('User-Agent')
            );

            return response()->json([
                'success' => true,
                'message' => 'Push subscription berhasil didaftarkan.',
                'subscription_id' => $subscription->id,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendaftarkan push notification: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Berhenti berlangganan (unsubscribe) Web Push
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => 'required|string',
        ]);

        $user = $request->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Autentikasi diperlukan.',
            ], 401);
        }

        $result = WebPushService::unsubscribe($user, $request->endpoint);

        return response()->json([
            'success' => true,
            'message' => $result ? 'Berhasil berhenti berlangganan.' : 'Subscription tidak ditemukan.',
        ]);
    }

    /**
     * Kirim push notification percobaan ke pengguna yang sedang login
     */
    public function testNotification(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Autentikasi diperlukan.',
            ], 401);
        }

        $subscriptionsCount = $user->pushSubscriptions()->count();
        if ($subscriptionsCount === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Perangkat ini belum terdaftar untuk push notification. Silakan aktifkan izin notifikasi terlebih dahulu.',
            ], 422);
        }

        $sent = WebPushService::sendTestNotification($user);

        return response()->json([
            'success' => $sent,
            'message' => $sent
                ? 'Notifikasi uji coba berhasil dikirim ke perangkat Anda!'
                : 'Notifikasi dikirim ke server push, silakan periksa status di HP/browser Anda.',
        ]);
    }
}
