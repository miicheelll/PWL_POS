<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'supplier_kode' => 'SUP001',
                'supplier_nama' => 'Supplier A',
                'supplier_alamat' => 'Jl. Merdeka No. 1, Jakarta',
            ],
            [
                'supplier_kode' => 'SUP002',
                'supplier_nama' => 'Supplier B',
                'supplier_alamat' => 'Jl. Sudirman No. 23, Bandung',
            ],
            [
                'supplier_kode' => 'SUP003',
                'supplier_nama' => 'Supplier C',
                'supplier_alamat' => 'Jl. Ahmad Yani No. 45, Surabaya',
            ],
        ];
        DB::table('m_supplier')->insertOrIgnore($data);
    }
}
