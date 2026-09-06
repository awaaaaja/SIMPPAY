<?php

namespace Database\Seeders;

use App\Models\PotonganGaji;
use Illuminate\Database\Seeder;

class PayrollSeeder extends Seeder
{
    public function run(): void
    {
        PotonganGaji::create([
            'nama_potongan' => 'Alpha',
            'tipe' => 'nominal',
            'nilai' => 50000,
            'is_alpha_penalty' => true,
            'aktif' => true,
        ]);
    }
}
