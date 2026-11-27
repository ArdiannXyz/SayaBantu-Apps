<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AppSetting extends Model
{
    protected $table = 'app_settings';

    protected $fillable = ['key', 'value'];

    public static function get($key, $default = null)
    {
        $row = static::where('key', $key)->first();

        return $row ? $row->value : $default;
    }

    public static function set($key, $value)
    {
        return static::updateOrCreate(['key' => $key], ['value' => (string) $value]);
    }

    public static function forget($key): int
    {
        return static::where('key', $key)->delete();
    }

    /**
     * Ambil URL gambar QRIS aktif (prioritaskan unggahan Super Admin di storage public, dengan cache-buster).
     */
    public static function qrisImageUrl(): ?string
    {
        $stored = static::get('qris_image');

        if ($stored && Storage::disk('public')->exists($stored)) {
            $version = @Storage::disk('public')->lastModified($stored) ?: time();

            return asset('storage/'.$stored).'?v='.$version;
        }

        $legacyPath = public_path('images/payment/qris.png');
        if (file_exists($legacyPath)) {
            $version = @filemtime($legacyPath) ?: time();

            return asset('images/payment/qris.png').'?v='.$version;
        }

        return null;
    }

    /**
     * Ambil konfigurasi metode pembayaran top-up aktif (QRIS & rekening bank/e-wallet riil) tanpa dummy fallback.
     *
     * @return array{qris_enabled: bool, banks: array<int, array<string, mixed>>}
     */
    public static function activePaymentConfig(): array
    {
        $raw = static::get('topup_payment_methods', '{}');
        $methods = json_decode((string) $raw, true) ?: [];
        $qrisEnabled = (bool) ($methods['qris']['enabled'] ?? true);

        $banks = $methods['banks'] ?? null;
        if (! is_array($banks)) {
            $banks = BankChannel::active()
                ->whereNotNull('account_number')
                ->where('account_number', '!=', '')
                ->get()
                ->map(fn ($channel) => [
                    'code' => strtolower($channel->code),
                    'name' => $channel->name,
                    'account_number' => $channel->account_number,
                    'account_name' => $channel->account_name ?: 'PT SayaBantu Indonesia',
                    'category' => $channel->category,
                    'enabled' => (bool) $channel->is_active,
                ])
                ->toArray();
        }

        $activeBanks = collect($banks)
            ->filter(fn ($bank) => ! empty($bank['enabled']) && ! empty($bank['account_number']))
            ->map(fn ($bank) => array_merge($bank, ['value' => 'bank_'.strtolower((string) ($bank['code'] ?? ''))]))
            ->values()
            ->toArray();

        return [
            'qris_enabled' => $qrisEnabled,
            'banks' => $activeBanks,
        ];
    }

    /**
     * Hitung biaya admin top-up berdasarkan pengaturan Super Admin (BiayaTopupBank).
     */
    public static function calculateTopupAdminFee(float|int $amount): int
    {
        $numericAmount = (float) $amount;
        if ($numericAmount <= 0) {
            return 0;
        }

        $tier1Limit = (int) static::get('topup_tier1_limit', 50000);
        $tier1Fee = (int) static::get('topup_tier1_fee', 5000);
        $tier2Limit = (int) static::get('topup_tier2_limit', 100000);
        $tier2Fee = (int) static::get('topup_tier2_fee', 7500);
        $tier3Pct = (float) static::get('topup_tier3_percentage', 3);
        $tier3Max = (int) static::get('topup_tier3_max', 15000);

        if ($numericAmount < $tier1Limit) {
            return (int) round($tier1Fee);
        }

        if ($numericAmount < $tier2Limit) {
            return (int) round($tier2Fee);
        }

        return (int) round(min($numericAmount * ($tier3Pct / 100), $tier3Max));
    }

    /**
     * Cek apakah tombol WhatsApp pada detail bantuan diaktifkan oleh Super Admin
     */
    public static function isWhatsappEnabled(): bool
    {
        $val = static::get('show_whatsapp_button', false);

        return filter_var($val, FILTER_VALIDATE_BOOLEAN);
    }
}
