<?php

namespace Database\Seeders;

use App\Models\BalanceTransaction;
use App\Models\User;
use App\Models\UserBalance;
use Illuminate\Database\Seeder;

class UserBalancesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus saldo untuk role superadmin dan admin wilayah agar bersih
        $adminUserIds = User::whereIn('role', ['super_admin', 'superadmin', 'admin', 'admin_wilayah'])->pluck('id');
        UserBalance::whereIn('user_id', $adminUserIds)->delete();
        User::whereIn('id', $adminUserIds)->update(['balance' => 0]);

        // Process only customer & mitra users in chunks to avoid memory issues
        User::whereIn('role', ['customer', 'kustomer', 'mitra'])->chunkById(200, function ($users) {
            foreach ($users as $user) {
                // Calculate completed topups and deductions
                // Normalize status checks: trim and lowercase to tolerate variations
                $topups = BalanceTransaction::where('user_id', $user->id)
                    ->where('type', 'topup')
                    ->whereRaw("LOWER(TRIM(COALESCE(status, ''))) = 'completed'")
                    ->sum('amount');

                $deductions = BalanceTransaction::where('user_id', $user->id)
                    ->where('type', 'deduction')
                    ->whereRaw("LOWER(TRIM(COALESCE(status, ''))) = 'completed'")
                    ->sum('amount');

                $balance = $topups - $deductions;

                // Create or update user balance
                UserBalance::updateOrCreate(
                    ['user_id' => $user->id],
                    ['balance' => $balance]
                );
            }
        });
    }
}
