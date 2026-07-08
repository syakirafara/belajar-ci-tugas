<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DiscountSeeder extends Seeder
{
    public function run()
    {
        // nominal diskon untuk 10 hari (boleh sama, yang penting TANGGAL harus unik)
        $nominals = [100000, 150000, 200000, 250000, 300000, 100000, 150000, 200000, 250000, 300000];

        $data = [];

        // mulai dari tanggal saat seeder dijalankan (hari ini) + 9 hari berikutnya
        for ($i = 0; $i < 10; $i++) {
            $data[] = [
                'tanggal'    => date('Y-m-d', strtotime("+$i day")),
                'nominal'    => $nominals[$i],
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }

        // insert semua data ke tabel discount
        $this->db->table('discount')->insertBatch($data);
    }
}
