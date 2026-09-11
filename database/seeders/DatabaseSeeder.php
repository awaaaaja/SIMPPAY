<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            // Reference tables (SOURCE: Rumusan Penggajian UA resmi)
            GolonganRuangSeeder::class,
            GajiPokokScaleSeeder::class,
            LevelStrukturSeeder::class,
            TunjanganTransportasiScaleSeeder::class,
            KlasifikasiJabatanStrukturalSeeder::class,
            JabatanStrukturalPoinSeeder::class,
            TunjanganFungsionalDosenScaleSeeder::class,
            TunjanganJabatanKaryawanScaleSeeder::class,
            TunjanganVariabelScaleSeeder::class,
            HonorSksScaleSeeder::class,
            BebanSksJabatanSeeder::class,
            // Existing data
            PayrollSeeder::class,
            DummySeeder::class,
        ]);

        $admin = User::where('username', 'admin')->first();
        if (! $admin) {
            $admin = User::factory()->create([
                'name' => 'Admin SIMPPAY',
                'username' => 'admin',
                'email' => 'admin@simppay.test',
            ]);
            $admin->assignRole('admin');
        }
    }
}
