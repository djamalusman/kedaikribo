<?php

if (! function_exists('rupiah')) {
    function rupiah($number, $withPrefix = true, $decimal = 0)
    {
        $number = $number ?? 0;

        // desimal: ','  ribuan: '.'
        $formatted = number_format((float) $number, $decimal, ',', '.');

        return $withPrefix ? 'Rp' . $formatted : $formatted;
    }
}
