<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StokSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['stok_id' => 1, 'supplier_id' => 1, 'barang_id' => 1, 'user_id' => 1, 'stok_tanggal' => now(), 'stok_jumlah' => 50],
            ['stok_id' => 2, 'supplier_id' => 2, 'barang_id' => 2, 'user_id' => 2, 'stok_tanggal' => now(), 'stok_jumlah' => 51],
            ['stok_id' => 3, 'supplier_id' => 3, 'barang_id' => 3, 'user_id' => 3, 'stok_tanggal' => now(), 'stok_jumlah' => 52],
            ['stok_id' => 4, 'supplier_id' => 1, 'barang_id' => 4, 'user_id' => 1, 'stok_tanggal' => now(), 'stok_jumlah' => 53],
            ['stok_id' => 5, 'supplier_id' => 2, 'barang_id' => 5, 'user_id' => 2, 'stok_tanggal' => now(), 'stok_jumlah' => 54],
            ['stok_id' => 6, 'supplier_id' => 3, 'barang_id' => 6, 'user_id' => 3, 'stok_tanggal' => now(), 'stok_jumlah' => 55],
            ['stok_id' => 7, 'supplier_id' => 1, 'barang_id' => 7, 'user_id' => 1, 'stok_tanggal' => now(), 'stok_jumlah' => 56],
            ['stok_id' => 8, 'supplier_id' => 2, 'barang_id' => 8, 'user_id' => 2, 'stok_tanggal' => now(), 'stok_jumlah' => 57],
            ['stok_id' => 9, 'supplier_id' => 3, 'barang_id' => 9, 'user_id' => 3, 'stok_tanggal' => now(), 'stok_jumlah' => 58],
            ['stok_id' => 10, 'supplier_id' => 1, 'barang_id' => 10, 'user_id' => 1, 'stok_tanggal' => now(), 'stok_jumlah' => 59],
            ['stok_id' => 11, 'supplier_id' => 2, 'barang_id' => 11, 'user_id' => 2, 'stok_tanggal' => now(), 'stok_jumlah' => 1],
            ['stok_id' => 12, 'supplier_id' => 3, 'barang_id' => 12, 'user_id' => 3, 'stok_tanggal' => now(), 'stok_jumlah' => 2],
            ['stok_id' => 13, 'supplier_id' => 1, 'barang_id' => 13, 'user_id' => 1, 'stok_tanggal' => now(), 'stok_jumlah' => 3],
            ['stok_id' => 14, 'supplier_id' => 2, 'barang_id' => 14, 'user_id' => 2, 'stok_tanggal' => now(), 'stok_jumlah' => 4],
            ['stok_id' => 15, 'supplier_id' => 3, 'barang_id' => 15, 'user_id' => 3, 'stok_tanggal' => now(), 'stok_jumlah' => 5],
        ];
        DB::table('t_stok')->insert($data);
    }
}
