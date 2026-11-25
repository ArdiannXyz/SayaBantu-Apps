<?php

namespace Tests\Feature\Interaction;

use App\Livewire\Admin\TopupApproval;
use App\Models\BalanceTransaction;
use App\Models\User;
use App\Models\UserBalance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TopupApprovalTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'verified' => true,
            'is_completed' => true,
            'email_verified_at' => now(),
        ]);
    }

    private function customer(): User
    {
        return User::factory()->create([
            'role' => 'customer',
            'status' => 'active',
            'verified' => true,
            'is_completed' => true,
            'email_verified_at' => now(),
        ]);
    }

    private function pendingTopup(User $customer, int $amount = 100000): BalanceTransaction
    {
        return BalanceTransaction::create([
            'user_id' => $customer->id,
            'amount' => $amount,
            'type' => 'topup',
            'status' => 'waiting_approval',
            'description' => 'Topup test',
        ]);
    }

    public function test_admin_menyetujui_topup_dan_saldo_bertambah(): void
    {
        $admin = $this->admin();
        $customer = $this->customer();
        $topup = $this->pendingTopup($customer, 100000);

        $this->actingAs($admin);

        Livewire::test(TopupApproval::class)->call('approve', $topup->id);

        $topup->refresh();
        $this->assertSame('completed', $topup->status);
        $this->assertSame($admin->id, $topup->approved_by);

        // Saldo dihitung ulang dari transaksi completed.
        $this->assertSame(100000.0, (float) UserBalance::recalculateForUser($customer->id));
        $this->assertSame(100000, $customer->fresh()->balance);
    }

    public function test_topup_yang_sudah_diproses_tidak_bisa_disetujui_lagi(): void
    {
        $admin = $this->admin();
        $customer = $this->customer();
        $topup = $this->pendingTopup($customer, 50000);

        $this->actingAs($admin);
        Livewire::test(TopupApproval::class)->call('approve', $topup->id);
        Livewire::test(TopupApproval::class)->call('approve', $topup->id);

        $this->assertSame(
            1,
            BalanceTransaction::where('user_id', $customer->id)->where('status', 'completed')->count()
        );
        $this->assertSame(50000, $customer->fresh()->balance);
    }

    public function test_admin_menolak_topup_tanpa_menambah_saldo(): void
    {
        $admin = $this->admin();
        $customer = $this->customer();
        $topup = $this->pendingTopup($customer, 75000);

        $this->actingAs($admin);

        Livewire::test(TopupApproval::class)
            ->call('viewDetail', $topup->id)
            ->set('rejectionReason', 'Bukti transfer tidak sesuai')
            ->call('reject');

        $topup->refresh();
        $this->assertSame('rejected', $topup->status);
        $this->assertSame('Bukti transfer tidak sesuai', $topup->rejection_reason);
        $this->assertSame(0, $customer->fresh()->balance);
    }

    public function test_penolakan_tanpa_alasan_ditolak_validasi(): void
    {
        $admin = $this->admin();
        $customer = $this->customer();
        $topup = $this->pendingTopup($customer, 20000);

        $this->actingAs($admin);

        Livewire::test(TopupApproval::class)
            ->call('viewDetail', $topup->id)
            ->set('rejectionReason', '')
            ->call('reject')
            ->assertHasErrors('rejectionReason');

        $this->assertSame('waiting_approval', $topup->fresh()->status);
    }

    public function test_mitra_topup_muncul_di_halaman_approval_admin_dan_riwayat_mitra(): void
    {
        $admin = $this->admin();
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'active',
            'verified' => true,
            'is_completed' => true,
            'email_verified_at' => now(),
        ]);
        $mitra = User::factory()->create([
            'role' => 'mitra',
            'name' => 'Hendra Wijaya',
            'status' => 'active',
            'verified' => true,
            'is_completed' => true,
            'email_verified_at' => now(),
        ]);

        // Pendapatan bantuan mitra TIDAK boleh masuk daftar topupRequests
        BalanceTransaction::create([
            'user_id' => $mitra->id,
            'amount' => 75000,
            'type' => 'topup',
            'status' => 'completed',
            'description' => 'Pendapatan Bantuan #99',
        ]);

        // Request top-up asli dari mitra HARUS muncul
        $mitraTopup = BalanceTransaction::create([
            'user_id' => $mitra->id,
            'amount' => 500000,
            'admin_fee' => 15000,
            'total_payment' => 515000,
            'type' => 'topup',
            'status' => 'waiting_approval',
            'description' => 'Top-up saldo via QRIS',
            'request_code' => 'TPU-20261125-001',
        ]);

        // 1. Tampil di Admin TopupApproval & bisa di-approve
        $this->actingAs($admin)
            ->get(route('admin.topup.approvals'))
            ->assertSuccessful()
            ->assertSee('TPU-20261125-001')
            ->assertSee('Hendra Wijaya')
            ->assertDontSee('Pendapatan Bantuan #99');

        // 2. Tampil di SuperAdmin TopupApproval
        $this->actingAs($superAdmin)
            ->get(route('superadmin.topup.approvals'))
            ->assertSuccessful()
            ->assertSee('TPU-20261125-001')
            ->assertSee('Hendra Wijaya');

        // 3. Tampil di Riwayat Top-Up Mitra
        $this->actingAs($mitra)
            ->get(route('mitra.topup.history'))
            ->assertSuccessful()
            ->assertSee('TPU-20261125-001')
            ->assertSee('500.000');

        // 4. Approve oleh Admin menambah saldo mitra dan mengirim notifikasi ber-URL mitra
        $this->actingAs($admin);
        Livewire::test(TopupApproval::class)->call('approve', $mitraTopup->id);

        $this->assertSame('completed', $mitraTopup->fresh()->status);
        $this->assertSame(575000, $mitra->fresh()->balance); // 75000 pendapatan + 500000 topup

        $latestNotif = $mitra->notifications()->latest()->first();
        $this->assertNotNull($latestNotif);
        $this->assertSame(route('mitra.topup.history'), $latestNotif->data['url']);
    }
}
