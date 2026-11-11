# SayaBantu

**Platform bantuan sosial on-demand** yang menghubungkan customer dengan mitra. Customer dapat mengajukan permintaan bantuan, mitra menangani pesanan, sementara admin wilayah dan super admin mengelola verifikasi, moderasi, serta pencairan dana.

Aplikasi ini dirancang sebagai web app **mobile-first** menggunakan Laravel 12 dan Livewire 3 (Volt), dengan antarmuka berlebar maksimum 420 px dan navigasi bawah.

## Daftar isi

- [Fitur](#fitur)
- [Teknologi](#teknologi)
- [Persyaratan](#persyaratan)
- [Menjalankan secara lokal](#menjalankan-secara-lokal)
- [Akun uji onboarding](#akun-uji-onboarding)
- [Proses background](#proses-background)
- [Alur bantuan](#alur-bantuan)
- [Struktur proyek](#struktur-proyek)
- [Testing](#testing)
- [Perintah berguna](#perintah-berguna)
- [Status fitur](#status-fitur)
- [Catatan keamanan](#catatan-keamanan)

## Fitur

- Registrasi dengan verifikasi email, login menggunakan email atau nomor HP, dan Google OAuth.
- Onboarding customer dan mitra, termasuk pengunggahan KTP dan verifikasi manual oleh admin.
- Permintaan bantuan terjadwal maupun urgent, dengan pelacakan perjalanan mitra.
- Chat customer–mitra, rating dua arah, laporan pelanggaran, dan sanksi.
- Saldo, top up dengan bukti transfer dan persetujuan admin, serta penarikan saldo (withdraw).
- Panel admin wilayah dan super admin, activity log, serta ekspor transaksi ke Excel.
- Pembatalan otomatis untuk permintaan kedaluwarsa dan konfirmasi otomatis setelah 24 jam.

## Teknologi

| Bagian | Teknologi |
|---|---|
| Backend | PHP 8.2+, Laravel 12 |
| Frontend | Livewire 3, Volt, Alpine.js, Tailwind CSS, Vite |
| Autentikasi | Laravel Breeze, verifikasi email, Google OAuth (Socialite) |
| Database | MySQL (produksi), SQLite (testing) |
| Pembayaran | Midtrans (kolom tersedia di `balance_transactions`) |

## Persyaratan

- PHP 8.2 atau lebih baru dengan ekstensi `pdo`, `mbstring`, `gd`, dan `fileinfo`
- Composer 2
- Node.js 18+ dan npm
- MySQL 8 atau MariaDB

## Menjalankan secara lokal

1. Pasang dependency:

   ```bash
   composer install
   npm install
   ```

2. Siapkan konfigurasi aplikasi:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   Isi konfigurasi database (`DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD`). Tambahkan kredensial Google OAuth jika ingin menggunakan fitur tersebut.

3. Jalankan migrasi dan seeder:

   ```bash
   php artisan migrate --seed
   ```

4. Siapkan storage dan aset frontend:

   ```bash
   php artisan storage:link
   npm run build
   ```

5. Jalankan aplikasi:

   ```bash
   php artisan serve
   ```

   Buka [http://127.0.0.1:8000](http://127.0.0.1:8000).

   Untuk menjalankan proses development sekaligus (server, queue, scheduler, log, dan Vite), gunakan:

   ```bash
   composer dev
   ```

### Login demo

`DEMO_LOGIN=true` di `.env` mengaktifkan toleransi password demo (`Sayabantu123!`, `sayabantu123`, atau `password`) bagi akun yang memang menggunakan salah satu password tersebut. Pengaturan ini **hanya untuk pengembangan lokal**. Jangan aktifkan di production; nilai default di `.env.example` adalah `false`.

### Akun uji onboarding

Akun berikut dibuat oleh `database/seeders/UserSeeder.php`. Password seluruh akun adalah `Sayabantu123!`. Statusnya `active`, tetapi `is_completed = false`, sehingga middleware `onboarded` akan mengarahkan pengguna ke tahap onboarding yang belum selesai.

| Email | Role | Tujuan setelah login |
|---|---|---|
| `customer.onboarding@sayabantu.com` | Customer | `onboarding/step1` (NIK masih kosong) |
| `customer.pending@sayabantu.com` | Customer | `onboarding/step2` (unggah KTP) |
| `mitra.pending@sayabantu.com` | Mitra | `onboarding/step2` (unggah KTP) |
| `yusuf.mitra@test.com` | Mitra | `onboarding/step2` (unggah KTP) |

`mitra.pending@sayabantu.com` dan `yusuf.mitra@test.com` juga memiliki entri `registrations` berstatus `pending_verification` untuk menguji antrean verifikasi KTP di panel admin.

## Proses background

Scheduler perlu berjalan agar tugas terjadwal aplikasi dapat diproses:

| Proses | Cara menjalankan | Kegunaan |
|---|---|---|
| Scheduler | `php artisan schedule:work` saat development, atau `schedule:run` setiap menit melalui cron di server | `helps:auto-confirm` (setiap 10 menit) dan `helps:auto-cancel` (setiap menit) |

Contoh cron untuk server:

```cron
* * * * * cd /path/to/sayabantu && php artisan schedule:run >> /dev/null 2>&1
```

Queue worker belum diperlukan karena notifikasi saat ini dikirim secara sinkron dan belum ada job yang di-dispatch. `QUEUE_CONNECTION` tetap dikonfigurasi untuk penggunaan di masa mendatang. Jika notifikasi kelak menggunakan `ShouldQueue`, jalankan `php artisan queue:work`.

## Alur bantuan

1. **Registrasi** — pengguna memilih role di `/register`; data akun disimpan di session.
2. **Onboarding** — empat langkah untuk data diri/KTP, foto KTP, selfie, dan review. Akun dibuat pada langkah terakhir.
3. **Verifikasi** — admin memeriksa data dan KTP; status akun memengaruhi kuota order.
4. **Permintaan bantuan** — customer membuat permintaan dan saldo ditahan. Mitra mengambil order, melakukan perjalanan, berkomunikasi melalui chat, lalu mengunggah bukti.
5. **Penyelesaian dan saldo** — customer mengonfirmasi pekerjaan selesai. Dana kemudian masuk ke saldo mitra dan dapat ditarik setelah persetujuan admin atau super admin.

Urutan status bantuan saat ini:

```text
menunggu_mitra -> memperoleh_mitra -> partner_on_the_way -> partner_arrived
-> in_progress -> waiting_customer_confirmation -> selesai
                                       `-> dibatalkan / rejected
```

Nilai lama (`approved`, `taken`, `completed`, `cancelled`, `pending`) tetap dikenali melalui `HelpStatus::aliases()` dan telah dinormalkan oleh migration.

## Struktur proyek

```text
app/
  Enums/HelpStatus.php              Sumber nilai status bantuan (termasuk alias legacy)
  Models/Help.php                   Model inti transaksi bantuan
  Models/Concerns/                  Trait status, timeline, payout, dan pembatalan mitra
  Livewire/{Customer,Mitra,Admin,SuperAdmin}/
  Livewire/Forms/LoginForm.php      Login dengan email atau nomor HP
  Support/JsonMaster.php            Cache master wilayah (districts/villages)
  Support/Sql.php                   Ekspresi SQL lintas database driver
  Observers/HelpObserver.php        Efek perubahan status, refund, dan pencairan saldo
  Console/Commands/                 Perintah bantuan dan maintenance

routes/
  web.php                           Rute publik, autentikasi/onboarding, dan rute bersama
  customer.php, mitra.php, admin.php, superadmin.php
  auth.php                           Login, Google OAuth, dan reset password
  console.php                        Jadwal scheduler

database/migrations/                Migration aplikasi, termasuk normalisasi status bantuan
scripts/                            Skrip utilitas manual; lihat scripts/README.md
```

## Testing

Test menggunakan SQLite in-memory melalui `phpunit.xml`, sehingga tidak mengubah database MySQL lokal.

Cakupan test saat ini meliputi autentikasi, verifikasi email, registrasi multi-step, profil, saldo dan withdraw, normalisasi status, serta smoke test halaman utama untuk setiap role (customer, mitra, admin, dan super admin).

## Perintah berguna

```bash
php artisan test                      # Jalankan seluruh test
php artisan test tests/Feature/Smoke  # Smoke test halaman per role
php artisan route:list                # Tampilkan daftar route
php artisan schedule:list             # Tampilkan jadwal aktif
php artisan helps:auto-cancel         # Jalankan pembatalan order kedaluwarsa
php artisan helps:auto-confirm        # Jalankan konfirmasi otomatis 24 jam
php artisan config:clear && php artisan cache:clear && php artisan view:clear
```

## Status fitur

### Sudah tersedia

- Registrasi dan verifikasi email, login email/nomor HP, serta Google OAuth.
- Onboarding KTP dan verifikasi manual oleh admin.
- Permintaan bantuan terjadwal/urgent, pembatalan otomatis, dan konfirmasi otomatis setelah 24 jam.
- Pelacakan perjalanan mitra menggunakan GPS, termasuk simulasi.
- Chat customer–mitra dengan polling 5 detik, rating dua arah, laporan pelanggaran, dan sanksi.
- Saldo, top up dengan bukti transfer dan persetujuan, serta withdraw dengan persetujuan.
- Panel admin wilayah dan super admin, activity log, serta ekspor transaksi ke Excel.

### Belum tersedia

- Notifikasi real-time (Pusher/Laravel Echo) dan push notification.
- Verifikasi KTP otomatis menggunakan OCR.
- Ekspor laporan PDF.

## Catatan keamanan

- Jangan gunakan `DEMO_LOGIN=true` di production.
- Pastikan `APP_DEBUG=false` di production. Nilai default di `.env.example` adalah `false`.
- Log menggunakan channel `daily` (`LOG_STACK=daily`) dengan retensi 14 hari.
- Aplikasi ini dibuat untuk tujuan pembelajaran dan sosial.
