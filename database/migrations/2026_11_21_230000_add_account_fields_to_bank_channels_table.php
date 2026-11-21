<?php

use App\Models\AppSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_channels', function (Blueprint $table) {
            if (! Schema::hasColumn('bank_channels', 'account_number')) {
                $table->string('account_number', 50)->nullable()->after('category');
            }
            if (! Schema::hasColumn('bank_channels', 'account_name')) {
                $table->string('account_name', 100)->nullable()->after('account_number');
            }
        });

        // Sinkronisasi data awal nomor rekening dari AppSetting topup_payment_methods jika ada
        $raw = AppSetting::get('topup_payment_methods', '{}');
        $methods = json_decode((string) $raw, true) ?: [];
        $existingBanks = $methods['banks'] ?? [];

        foreach ($existingBanks as $bank) {
            $code = strtoupper($bank['code'] ?? '');
            if (! empty($code)) {
                DB::table('bank_channels')
                    ->where('code', $code)
                    ->update([
                        'account_number' => $bank['account_number'] ?? null,
                        'account_name' => $bank['account_name'] ?? 'PT SayaBantu Indonesia',
                        'updated_at' => now(),
                    ]);
            }
        }

        // Default rekening untuk bank utama bila masih kosong
        $defaults = [
            'BCA' => ['number' => '1234567890', 'name' => 'PT SayaBantu Indonesia'],
            'MANDIRI' => ['number' => '0987654321', 'name' => 'PT SayaBantu Indonesia'],
            'BNI' => ['number' => '5555666677', 'name' => 'PT SayaBantu Indonesia'],
            'BRI' => ['number' => '8888999900', 'name' => 'PT SayaBantu Indonesia'],
            'DANA' => ['number' => '081234567890', 'name' => 'PT SayaBantu Indonesia'],
            'GOPAY' => ['number' => '081234567890', 'name' => 'PT SayaBantu Indonesia'],
            'OVO' => ['number' => '081234567890', 'name' => 'PT SayaBantu Indonesia'],
            'SHOPEEPAY' => ['number' => '081234567890', 'name' => 'PT SayaBantu Indonesia'],
        ];

        foreach ($defaults as $code => $data) {
            DB::table('bank_channels')
                ->where('code', $code)
                ->whereNull('account_number')
                ->update([
                    'account_number' => $data['number'],
                    'account_name' => $data['name'],
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('bank_channels', function (Blueprint $table) {
            if (Schema::hasColumn('bank_channels', 'account_number')) {
                $table->dropColumn('account_number');
            }
            if (Schema::hasColumn('bank_channels', 'account_name')) {
                $table->dropColumn('account_name');
            }
        });
    }
};
