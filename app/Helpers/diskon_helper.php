<?php

use App\Models\DiscountModel;

if (!function_exists('diskon_hari_ini')) {
    /**
     * Mengembalikan nominal diskon yang berlaku pada tanggal user
     * mengakses sistem (hari ini). Mengembalikan 0 jika tidak ada diskon.
     *
     * Nilai di-cache selama satu request (static) agar query hanya
     * dijalankan sekali, dan juga disimpan ke Session library
     * (sesuai hint pada soal).
     */
    function diskon_hari_ini(): float
    {
        static $nominal = null;

        if ($nominal !== null) {
            return $nominal;
        }

        $model  = new DiscountModel();
        $diskon = $model->where('tanggal', date('Y-m-d'))->first();

        $nominal = $diskon ? (float) $diskon['nominal'] : 0.0;

        // simpan ke Session library
        session()->set('diskon_hari_ini', $nominal);

        return $nominal;
    }
}

if (!function_exists('harga_diskon')) {
    /**
     * Mengembalikan harga produk setelah dikurangi diskon hari ini.
     * Tidak pernah bernilai kurang dari 0.
     */
    function harga_diskon($harga): float
    {
        $hasil = (float) $harga - diskon_hari_ini();

        return $hasil < 0 ? 0.0 : $hasil;
    }
}
