<?php

namespace App\Support;

class Currency
{
    /**
     * Format nominal uang ke bentuk ringkas Bahasa Indonesia tanpa pembulatan.
     * Nilai >= 1.000.000 yang habis dibagi 10.000 (maks. 2 desimal pas) diformat
     * dengan akhiran " jt" (contoh: 1000000 -> "1 jt", 1500000 -> "1,5 jt", 1250000 -> "1,25 jt").
     * Nilai dengan sisa ratusan/ribuan ganjil (contoh: 12500500 -> "12.500.500")
     * atau di bawah 1.000.000 tetap ditampilkan utuh dengan pemisah ribuan titik.
     */
    public static function formatNominal(int|float|string|null $amount): string
    {
        $numeric = is_numeric($amount) ? (float) $amount : 0.0;
        $sign = $numeric < 0 ? '-' : '';
        $abs = abs($numeric);

        if ($abs >= 1_000_000 && abs($abs - round($abs)) < 1e-6 && fmod(round($abs), 10000.0) === 0.0) {
            $millions = round($abs) / 1_000_000;
            $formatted = number_format($millions, 2, ',', '.');
            if (str_contains($formatted, ',')) {
                $formatted = rtrim(rtrim($formatted, '0'), ',');
            }

            return $sign . $formatted . ' jt';
        }

        return $sign . number_format($abs, 0, ',', '.');
    }

    /**
     * Format nominal uang beserta awalan "Rp " (atau "Rp" tanpa spasi).
     */
    public static function formatRupiah(int|float|string|null $amount, bool $withSpace = true): string
    {
        $numeric = is_numeric($amount) ? (float) $amount : 0.0;
        $sign = $numeric < 0 ? '-' : '';
        $prefix = $withSpace ? 'Rp ' : 'Rp';

        return $sign . $prefix . self::formatNominal(abs($numeric));
    }
}
