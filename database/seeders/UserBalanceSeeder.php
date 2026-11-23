<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserBalance;
use Illuminate\Database\Seeder;

class UserBalanceSeeder extends Seeder
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

        // Inisialisasi saldo hanya untuk customer dan mitra
        $users = User::whereIn('role', ['customer', 'kustomer', 'mitra'])->get();

        foreach ($users as $user) {
            UserBalance::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0]
            );
        }
    }
}
