<?php

namespace Tests\Unit;

use App\Support\Currency;
use Tests\TestCase;

class CurrencyFormatterTest extends TestCase
{
    public function test_formats_amounts_below_one_million_with_dot_separator(): void
    {
        $this->assertSame('0', format_nominal(0));
        $this->assertSame('Rp 0', format_rupiah(0));
        $this->assertSame('500.000', format_nominal(500000));
        $this->assertSame('Rp 500.000', format_rupiah(500000));
        $this->assertSame('Rp 999.999', format_rupiah(999999));
        $this->assertSame('Rp10.000', format_rupiah(10000, false));
    }

    public function test_formats_exact_millions_as_jt_without_trailing_zeros(): void
    {
        $this->assertSame('1 jt', format_nominal(1000000));
        $this->assertSame('Rp 1 jt', format_rupiah(1000000));
        $this->assertSame('Rp1 jt', format_rupiah(1000000, false));
        $this->assertSame('Rp 2 jt', format_rupiah(2000000));
        $this->assertSame('Rp 10 jt', format_rupiah(10000000));
        $this->assertSame('-Rp 1 jt', Currency::formatRupiah(-1000000));
    }

    public function test_formats_fractional_millions_with_comma_and_no_trailing_zero(): void
    {
        $this->assertSame('1,5 jt', format_nominal(1500000));
        $this->assertSame('Rp 1,5 jt', format_rupiah(1500000));
        $this->assertSame('Rp 1,25 jt', format_rupiah(1250000));
        $this->assertSame('Rp 2,1 jt', format_rupiah(2100000));
        $this->assertSame('Rp 10,5 jt', format_rupiah(10500000));
        $this->assertSame('Rp 12,5 jt', format_rupiah(12500000));
    }

    public function test_preserves_full_nominal_without_rounding_when_non_exact(): void
    {
        $this->assertSame('12.500.500', format_nominal(12500500));
        $this->assertSame('Rp 12.500.500', format_rupiah(12500500));
        $this->assertSame('Rp 1.005.000', format_rupiah(1005000));
        $this->assertSame('Rp 1.000.500', format_rupiah(1000500));
        $this->assertSame('Rp 1.234.567', format_rupiah(1234567));
    }
}
