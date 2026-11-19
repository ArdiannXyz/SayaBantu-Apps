<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\User;
use App\Models\UserBalance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Membuat 6 akun resmi siap pakai untuk deployment:
     * 1. Super Admin (Nasional)
     * 2. Admin DI Yogyakarta
     * 3. Hendra Wijaya (Mitra)
     * 4. Doni Setiawan (Mitra)
     * 5. Budi Hartono (Customer)
     * 6. Dewi Rahayu (Customer)
     *
     * Password: Sayabantu123!
     * PIN Transaksi: 123456
     * Seluruh saldo awal: Rp 0
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('Sayabantu123!');
        $defaultPin = Hash::make('123456');

        $columns = Schema::getColumnListing('users');
        $hasOccupation = in_array('occupation', $columns);
        $hasPin = in_array('transaction_pin', $columns);

        // Ambil data kota referensi DI Yogyakarta
        $kotaYogya = City::where('name', 'like', '%Kota Yogyakarta%')->first() ?: City::find(31);
        $sleman = City::where('name', 'like', '%Sleman%')->first() ?: City::find(32);
        $bantul = City::where('name', 'like', '%Bantul%')->first() ?: City::find(33);
        $gunungkidul = City::where('name', 'like', '%Gunungkidul%')->first() ?: City::find(34);
        $kulonprogo = City::where('name', 'like', '%Kulonprogo%')->first() ?: City::find(35);

        $yogyaId = $kotaYogya ? $kotaYogya->id : 31;
        $yogyaName = $kotaYogya ? $kotaYogya->name : 'Kota Yogyakarta';
        $yogyaProv = $kotaYogya ? ($kotaYogya->province ?? 'DI Yogyakarta') : 'DI Yogyakarta';

        $slemanId = $sleman ? $sleman->id : 32;
        $slemanName = $sleman ? $sleman->name : 'Kabupaten Sleman';
        $slemanProv = $sleman ? ($sleman->province ?? 'DI Yogyakarta') : 'DI Yogyakarta';

        $bantulId = $bantul ? $bantul->id : 33;
        $gunungkidulId = $gunungkidul ? $gunungkidul->id : 34;
        $kulonprogoId = $kulonprogo ? $kulonprogo->id : 35;

        $diyCityIds = array_values(array_filter([$yogyaId, $slemanId, $bantulId, $gunungkidulId, $kulonprogoId]));

        $accounts = [
            // =========================================================================
            // 1. SUPER ADMIN (Nasional)
            // =========================================================================
            [
                'email' => 'superadmin@sayabantu.com',
                'name' => 'Super Admin (Nasional)',
                'password' => $defaultPassword,
                'role' => 'super_admin',
                'verified' => true,
                'status' => 'active',
                'phone' => '081200000001',
                'nik' => '3171010101900001',
                'city_id' => $yogyaId,
                'city' => 'Nasional (Seluruh Indonesia)',
                'province' => 'Indonesia',
                'address' => 'Kantor Pusat SayaBantu Indonesia',
                'is_completed' => true,
                'email_verified_at' => now(),
                'transaction_pin' => $defaultPin,
            ],

            // =========================================================================
            // 2. ADMIN DI YOGYAKARTA
            // =========================================================================
            [
                'email' => 'admin.jogja@sayabantu.com',
                'name' => 'Admin DI Yogyakarta',
                'password' => $defaultPassword,
                'role' => 'admin',
                'city_id' => $yogyaId,
                'city' => $yogyaName,
                'province' => $yogyaProv,
                'verified' => true,
                'status' => 'active',
                'phone' => '081234562020',
                'nik' => '3471010101900020',
                'address' => 'Kantor Wilayah DIY, Jl. Malioboro No. 56, Yogyakarta',
                'is_completed' => true,
                'email_verified_at' => now(),
                'transaction_pin' => $defaultPin,
            ],

            // =========================================================================
            // 3. MITRA 1: HENDRA WIJAYA (Kota Yogyakarta)
            // =========================================================================
            [
                'email' => 'hendra.mitra@test.com',
                'name' => 'Hendra Wijaya',
                'password' => $defaultPassword,
                'role' => 'mitra',
                'gender' => 'Laki-laki',
                'city_id' => $yogyaId,
                'city' => $yogyaName,
                'province' => $yogyaProv,
                'kecamatan' => 'Gondokusuman',
                'kelurahan' => 'Kotabaru',
                'verified' => true,
                'status' => 'active',
                'phone' => '081234561001',
                'nik' => '3471012010910001',
                'address' => 'Jl. Kotabaru No. 10, Gondokusuman, Kota Yogyakarta',
                'is_completed' => true,
                'email_verified_at' => now(),
                'transaction_pin' => $defaultPin,
            ],

            // =========================================================================
            // 4. MITRA 2: DONI SETIAWAN (Kabupaten Sleman)
            // =========================================================================
            [
                'email' => 'doni.mitra@test.com',
                'name' => 'Doni Setiawan',
                'password' => $defaultPassword,
                'role' => 'mitra',
                'gender' => 'Laki-laki',
                'city_id' => $slemanId,
                'city' => $slemanName,
                'province' => $slemanProv,
                'kecamatan' => 'Mlati',
                'kelurahan' => 'Sinduadi',
                'verified' => true,
                'status' => 'active',
                'phone' => '081234561003',
                'nik' => '3404012508930003',
                'address' => 'Jl. Magelang Km 4.5, Sinduadi, Mlati, Sleman',
                'is_completed' => true,
                'email_verified_at' => now(),
                'transaction_pin' => $defaultPin,
            ],

            // =========================================================================
            // 5. CUSTOMER 1: BUDI HARTONO (Kabupaten Sleman)
            // =========================================================================
            [
                'email' => 'budi.sleman@test.com',
                'name' => 'Budi Hartono',
                'password' => $defaultPassword,
                'role' => 'customer',
                'gender' => 'Laki-laki',
                'city_id' => $slemanId,
                'city' => $slemanName,
                'province' => $slemanProv,
                'kecamatan' => 'Depok',
                'kelurahan' => 'Caturtunggal',
                'verified' => true,
                'status' => 'active',
                'phone' => '081234560002',
                'nik' => '3404011505920002',
                'address' => 'Jl. Gejayan No. 88, Caturtunggal, Depok, Sleman',
                'is_completed' => true,
                'email_verified_at' => now(),
                'transaction_pin' => $defaultPin,
            ],

            // =========================================================================
            // 6. CUSTOMER 2: DEWI RAHAYU (Kota Yogyakarta)
            // =========================================================================
            [
                'email' => 'dewi.jogja@test.com',
                'name' => 'Dewi Rahayu',
                'password' => $defaultPassword,
                'role' => 'customer',
                'gender' => 'Perempuan',
                'city_id' => $yogyaId,
                'city' => $yogyaName,
                'province' => $yogyaProv,
                'kecamatan' => 'Gondokusuman',
                'kelurahan' => 'Terban',
                'verified' => true,
                'status' => 'active',
                'phone' => '081234560001',
                'nik' => '3471015505950001',
                'address' => 'Jl. C. Simanjuntak No. 25, Terban, Gondokusuman, Kota Yogyakarta',
                'is_completed' => true,
                'email_verified_at' => now(),
                'transaction_pin' => $defaultPin,
            ],
        ];

        foreach ($accounts as $data) {
            unset($data['initial_balance']);

            if ($hasOccupation && ! isset($data['occupation'])) {
                $data['occupation'] = ($data['role'] === 'mitra') ? 'Teknisi / Rekan Jasa' : 'Karyawan Swasta';
            }

            if ($hasPin && ! isset($data['transaction_pin'])) {
                $data['transaction_pin'] = $defaultPin;
            }

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                $data
            );

            // Hubungkan Admin ke seluruh kota-kota kelolaannya di wilayah DIY
            if ($user->role === 'admin' && ! empty($user->city_id)) {
                $user->managedCities()->sync($diyCityIds);
            }

            // Pastikan saldo awal adalah 0 untuk seluruh pengguna
            if (in_array($user->role, ['customer', 'kustomer', 'mitra'])) {
                UserBalance::updateOrCreate(
                    ['user_id' => $user->id],
                    ['balance' => 0]
                );
                $user->forceFill(['balance' => 0])->saveQuietly();
            }
        }
    }
}
