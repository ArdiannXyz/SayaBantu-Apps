<?php

namespace App\Providers;

use App\Models\Help;
use App\Models\Rating;
use App\Models\User;
use App\Observers\HelpObserver;
use App\Observers\RatingObserver;
use App\Observers\UserObserver;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(Redirector $redirect): void
    {
        // Register Help Observer
        Help::observe(HelpObserver::class);

        // Register User & Rating observers for activity logging
        User::observe(UserObserver::class);
        Rating::observe(RatingObserver::class);

        // Register BalanceTransaction observer to update user balances when transactions complete
        \App\Models\BalanceTransaction::observe(\App\Observers\BalanceTransactionObserver::class);

        // Otomatis teruskan setiap notifikasi database ke Web Push Notification pengguna
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Notifications\Events\NotificationSent::class,
            function (\Illuminate\Notifications\Events\NotificationSent $event) {
                if ($event->notifiable instanceof \App\Models\User && $event->channel === 'database') {
                    try {
                        $notif = $event->notification;
                        $user = $event->notifiable;
                        $data = is_array($event->response)
                            ? $event->response
                            : (method_exists($notif, 'toDatabase') ? $notif->toDatabase($user) : (method_exists($notif, 'toArray') ? $notif->toArray($user) : []));

                        $notifId = is_object($event->response) && isset($event->response->id)
                            ? (string) $event->response->id
                            : (isset($notif->id) ? (string) $notif->id : ($data['id'] ?? null));

                        $resolvedData = $notifId ? array_merge(['id' => $notifId], $data) : $data;
                        $title = $data['title'] ?? null;
                        $body = $data['message'] ?? ($data['description'] ?? ($data['body'] ?? ($data['help_title'] ?? null)));
                        $url = \App\Services\NotificationUrlResolver::resolveUrl($resolvedData, $user);

                        if (! $title && isset($data['type'])) {
                            $title = match (true) {
                                $data['type'] === 'chat_message' => '💬 Pesan Baru dari ' . ($data['from_name'] ?? 'Pengguna'),
                                $data['type'] === 'help_request' => '📢 Permintaan Bantuan Baru',
                                str_contains($data['type'], 'topup') => '💰 Update Top-Up Saldo',
                                str_contains($data['type'], 'withdraw') => '🏧 Update Tarik Saldo',
                                default => '🔔 Notifikasi SayaBantu',
                            };
                        }

                        if (! $title) {
                            $title = '🔔 Notifikasi SayaBantu';
                        }

                        if ($body) {
                            \App\Services\WebPushService::sendToUser(
                                $user,
                                $title,
                                (string) $body,
                                $url,
                                null,
                                'sayabantu-notif-' . ($notifId ?: uniqid('', true)),
                                [
                                    'notifType' => $data['type'] ?? 'info',
                                    'isUrgent' => (($data['help_type'] ?? '') === 'urgent')
                                        || (($data['type'] ?? '') === 'idle_partner_alert'),
                                ]
                            );
                        }
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning('[AppServiceProvider] Gagal mirror notifikasi ke Web Push: ' . $e->getMessage());
                    }
                }
            }
        );

        // Force HTTPS in production or behind secure proxies to prevent Mixed Content warnings on iOS/Safari
        if ($this->app->environment('production') || request()->header('X-Forwarded-Proto') === 'https') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Redirect authenticated users based on their role
        $this->configureRedirectsForAuthentication();
    }

    /**
     * Configure redirects after authentication
     */
    private function configureRedirectsForAuthentication(): void
    {
        Redirector::macro('intended', function ($default = '/', $status = 302, $headers = [], $secure = null) {
            $intended = session()->pull('url.intended');

            if ($intended && Auth::check()) {
                $user = Auth::user();
                $path = parse_url($intended, PHP_URL_PATH) ?? '';
                $valid = true;

                if (in_array($user->role, ['customer', 'kustomer'])) {
                    if (str_starts_with($path, '/admin') || str_starts_with($path, '/superadmin') || str_starts_with($path, '/mitra')) {
                        $valid = false;
                    }
                } elseif ($user->role === 'mitra') {
                    if (str_starts_with($path, '/admin') || str_starts_with($path, '/superadmin')) {
                        $valid = false;
                    }
                } elseif ($user->role === 'admin') {
                    if (str_starts_with($path, '/superadmin')) {
                        $valid = false;
                    }
                }

                if ($valid) {
                    return redirect()->to($intended, $status, $headers, $secure);
                }
            } elseif ($intended && ! Auth::check()) {
                return redirect()->to($intended, $status, $headers, $secure);
            }

            // Check user role and redirect accordingly
            if (Auth::check()) {
                $user = Auth::user();
                if ($user->role === 'mitra') {
                    return redirect()->to(route('mitra.dashboard'), $status, $headers, $secure);
                } elseif (in_array($user->role, ['admin', 'super_admin'])) {
                    return redirect()->to(
                        $user->role === 'super_admin' ? route('superadmin.dashboard') : route('admin.dashboard'),
                        $status,
                        $headers,
                        $secure
                    );
                } elseif (in_array($user->role, ['customer', 'kustomer'])) {
                    return redirect()->to(route('customer.dashboard'), $status, $headers, $secure);
                }
            }

            return redirect()->to($default, $status, $headers, $secure);
        });
    }
}
