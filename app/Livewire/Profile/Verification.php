<?php

namespace App\Livewire\Profile;

use App\Models\City;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class Verification extends Component
{
    use WithFileUploads;

    public string $nik = '';

    public $ktp_photo;

    public $selfie_photo;

    public $existing_ktp;

    public $existing_selfie;

    public bool $isVerified = false;

    public $rejection_reason = null;

    public $is_rejected = false;

    public function mount()
    {
        $user = Auth::user();
        $this->nik = (string) ($user->nik ?? '');
        $this->existing_ktp = $user->ktp_photo;
        $this->existing_selfie = $user->selfie_photo;
        $this->isVerified = (bool) ($user->verified ?? false);

        $reg = Registration::where('email', $user->email)->first();
        if ($reg && $reg->status === 'rejected') {
            $this->is_rejected = true;
            $this->rejection_reason = $reg->rejection_reason ?: 'Dokumen tidak memenuhi persyaratan verifikasi.';
        }
    }

    public function save()
    {
        $user = Auth::user();
        $this->nik = preg_replace('/\D/', '', $this->nik);

        $rules = [
            'ktp_photo' => (empty($user->ktp_photo) ? 'required|' : 'nullable|').'image|max:15360', // 15MB Max
            'selfie_photo' => (empty($user->selfie_photo) ? 'required|' : 'nullable|').'image|max:15360', // 15MB Max
        ];

        if (! $user->verified) {
            $rules['nik'] = [
                'required',
                'digits:16',
                Rule::unique('users', 'nik')->ignore($user->id),
                function (string $attribute, mixed $value, \Closure $fail) use ($user) {
                    $existsInReg = Registration::where('nik', $value)
                        ->where('email', '!=', $user->email)
                        ->where('status', '!=', 'rejected')
                        ->exists();
                    if ($existsInReg) {
                        $fail('NIK ini sudah terdaftar pada pengajuan akun lain.');
                    }
                },
            ];
        }

        $this->validate($rules, [
            'nik.required' => 'NIK wajib diisi untuk verifikasi KTP.',
            'nik.digits' => 'NIK harus terdiri dari tepat 16 digit angka.',
            'nik.unique' => 'NIK ini sudah digunakan oleh akun lain.',
            'ktp_photo.required' => 'Foto KTP wajib diunggah.',
            'ktp_photo.image' => 'File KTP harus berupa gambar.',
            'ktp_photo.max' => 'Ukuran gambar KTP maksimal 15MB.',
            'selfie_photo.required' => 'Foto Selfie dengan KTP wajib diunggah.',
            'selfie_photo.image' => 'File Selfie harus berupa gambar.',
            'selfie_photo.max' => 'Ukuran gambar Selfie maksimal 15MB.',
        ]);

        $changes = 0;

        if (! $user->verified && $this->nik !== '' && $this->nik !== (string) $user->nik) {
            $user->nik = $this->nik;
            $changes++;
        }

        if ($this->ktp_photo) {
            // Hapus yang lama jika ada
            if ($user->ktp_photo && Storage::disk('public')->exists($user->ktp_photo)) {
                Storage::disk('public')->delete($user->ktp_photo);
            }
            $path = \App\Services\ImageCompressionService::compressAndStore($this->ktp_photo, 'ktp-photos');
            $user->ktp_photo = $path;
            $this->existing_ktp = $path;
            $changes++;
        }

        if ($this->selfie_photo) {
            // Hapus yang lama jika ada
            if ($user->selfie_photo && Storage::disk('public')->exists($user->selfie_photo)) {
                Storage::disk('public')->delete($user->selfie_photo);
            }
            $path = \App\Services\ImageCompressionService::compressAndStore($this->selfie_photo, 'selfie-photos');
            $user->selfie_photo = $path;
            $this->existing_selfie = $path;
            $changes++;
        }

        if ($changes > 0 || ($user->ktp_photo && $user->selfie_photo)) {
            if ($changes > 0) {
                $user->save();
            }

            // Sinkronkan ke tabel registrations agar muncul di admin verifikasi KTP
            $this->syncToRegistrations($user);

            // Kirim notifikasi ke Admin Kota terkait
            try {
                $cityId = $user->city_id;
                if (! $cityId && ! empty($user->city)) {
                    $cityId = City::where('name', 'like', '%'.trim($user->city).'%')->value('id');
                    if ($cityId) {
                        $user->city_id = $cityId;
                        $user->save();
                    }
                }
                if ($cityId) {
                    $cityAdmins = User::getAdminsForCity($cityId);
                    if ($cityAdmins->isNotEmpty()) {
                        \Illuminate\Support\Facades\Notification::send($cityAdmins, new \App\Notifications\NewKtpVerificationNotification($user));
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Gagal kirim notif KTP baru ke admin kota: '.$e->getMessage());
            }

            // Kirim notifikasi ke user bahwa KTP sedang ditinjau
            try {
                $user->notify(new \App\Notifications\KtpVerificationPromptNotification);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Gagal kirim notif status KTP ke user: '.$e->getMessage());
            }

            session()->flash('message', 'Data verifikasi berhasil diperbarui dan dikirim ke Admin.');

            if (Auth::user()->role === 'mitra') {
                return $this->redirectRoute('mitra.profile', navigate: true);
            }

            return $this->redirectRoute('profile', navigate: true);
        } else {
            session()->flash('error', 'Pilih minimal satu file (KTP atau Selfie) yang ingin diunggah sebelum menyimpan.');
        }
    }

    /**
     * Buat atau update record di tabel registrations berdasarkan data user.
     * Sehingga data muncul di halaman Verifikasi KTP admin.
     */
    protected function syncToRegistrations($user)
    {
        $existing = Registration::where('email', $user->email)->first();

        // Cari nama kota jika user->city kosong tapi city_id terisi
        $cityName = $user->city;
        if (empty($cityName) && ! empty($user->city_id)) {
            $cityObj = City::find($user->city_id);
            $cityName = $cityObj ? $cityObj->name : null;
        }

        $role = $user->role;
        if ($role === 'kustomer' || $role === 'customer') {
            $role = 'customer';
        } else {
            $role = 'mitra';
        }

        if ($existing) {
            $existing->update([
                'role' => $role,
                'nik' => $user->nik ?: $existing->nik,
                'ktp_photo_path' => $user->ktp_photo ?? $existing->ktp_photo_path,
                'selfie_photo_path' => $user->selfie_photo ?? $existing->selfie_photo_path,
                'city_id' => $user->city_id ?? $existing->city_id,
                'city' => $cityName ?? $existing->city,
                'status' => 'pending_verification',
                'rejection_reason' => null,
            ]);
        } else {
            Registration::create([
                'uuid' => (string) Str::uuid(),
                'role' => $role,
                'full_name' => $user->name,
                'email' => $user->email,
                'nik' => $user->nik,
                'place_of_birth' => $user->place_of_birth,
                'date_of_birth' => $user->date_of_birth,
                'gender' => $user->gender,
                'address' => $user->address,
                'rt' => $user->rt,
                'rw' => $user->rw,
                'kelurahan' => $user->kelurahan,
                'kecamatan' => $user->kecamatan,
                'city' => $cityName,
                'city_id' => $user->city_id,
                'province' => $user->province,
                'religion' => $user->religion,
                'marital_status' => $user->marital_status,
                'occupation' => $user->occupation,
                'ktp_photo_path' => $user->ktp_photo,
                'selfie_photo_path' => $user->selfie_photo,
                'status' => 'pending_verification',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.profile.verification')->layout('layouts.app');
    }
}
