<?php

use App\Support\Currency;

if (! function_exists('format_nominal')) {
    function format_nominal(int|float|string|null $amount): string
    {
        return Currency::formatNominal($amount);
    }
}

if (! function_exists('format_rupiah')) {
    function format_rupiah(int|float|string|null $amount, bool $withSpace = true): string
    {
        return Currency::formatRupiah($amount, $withSpace);
    }
}
