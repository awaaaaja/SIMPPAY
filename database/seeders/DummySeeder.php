<?php

namespace Database\Seeders;

use App\Models\Fungsional;
use App\Models\Jabatan;
use App\Models\Kehadiran;
use App\Models\PayrollDetail;
use App\Models\PayrollRun;
use App\Models\Pegawai;
use App\Models\PegawaiAnak;
use App\Models\PotonganGaji;
use App\Models\Struktural;
use App\Models\TunjanganGaji;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DummySeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('username', 'admin')->first();
        if (! $admin) {
            $admin = User::create([
                'name' => 'Admin SIMPPAY',
                'username' => 'admin',
                'email' => 'admin@simppay.test',
                'password' => Hash::make('password'),
            ]);
            $admin->assignRole('admin');
        }

        $bpsdmUser = User::where('username', 'bpsdm')->first();
        if (! $bpsdmUser) {
            $bpsdmUser = User::create([
                'name' => 'BPSDM Operator',
                'username' => 'bpsdm',
                'email' => 'bpsdm@simppay.test',
                'password' => Hash::make('password'),
            ]);
            $bpsdmUser->assignRole('bpsdm');
        }

        $jabatans = [
            Jabatan::firstOrCreate(['nama_jabatan' => 'Dosen Tetap'], [
                'gaji_pokok' => 8500000, 'tj_transport' => 1500000, 'uang_makan' => 1000000,
            ]),
            Jabatan::firstOrCreate(['nama_jabatan' => 'Dosen PKDLS'], [
                'gaji_pokok' => 7000000, 'tj_transport' => 1200000, 'uang_makan' => 800000,
            ]),
            Jabatan::firstOrCreate(['nama_jabatan' => 'Tenaga Kependidikan'], [
                'gaji_pokok' => 4500000, 'tj_transport' => 800000, 'uang_makan' => 600000,
            ]),
            Jabatan::firstOrCreate(['nama_jabatan' => 'Staf Administrasi'], [
                'gaji_pokok' => 4000000, 'tj_transport' => 700000, 'uang_makan' => 500000,
            ]),
            Jabatan::firstOrCreate(['nama_jabatan' => 'Cleaning Service'], [
                'gaji_pokok' => 3000000, 'tj_transport' => 500000, 'uang_makan' => 400000,
            ]),
        ];

        Struktural::firstOrCreate(['nama_struktural' => 'Rektor'], ['level_struktural' => 1, 'status' => 'aktif']);
        Struktural::firstOrCreate(['nama_struktural' => 'Wakil Rektor I'], ['level_struktural' => 2, 'status' => 'aktif']);
        Struktural::firstOrCreate(['nama_struktural' => 'Wakil Rektor II'], ['level_struktural' => 2, 'status' => 'aktif']);
        Struktural::firstOrCreate(['nama_struktural' => 'Dekan Fakultas Ekonomi'], ['level_struktural' => 3, 'status' => 'aktif']);
        Struktural::firstOrCreate(['nama_struktural' => 'Dekan Fakultas Teknik'], ['level_struktural' => 3, 'status' => 'aktif']);
        Struktural::firstOrCreate(['nama_struktural' => 'Kaprodi Informatika'], ['level_struktural' => 4, 'status' => 'aktif']);
        Struktural::firstOrCreate(['nama_struktural' => 'Kaprodi Manajemen'], ['level_struktural' => 4, 'status' => 'aktif']);

        Fungsional::firstOrCreate(['nama_fungsional' => 'Asisten Ahli'], ['angka_kredit' => 0, 'pangkat' => 'Penata Muda III/a', 'golongan' => 'III/a']);
        Fungsional::firstOrCreate(['nama_fungsional' => 'Lektor'], ['angka_kredit' => 50, 'pangkat' => 'Penata Muda Tk.I III/b', 'golongan' => 'III/b']);
        Fungsional::firstOrCreate(['nama_fungsional' => 'Lektor Kepala'], ['angka_kredit' => 100, 'pangkat' => 'Penata III/c', 'golongan' => 'III/c']);
        Fungsional::firstOrCreate(['nama_fungsional' => 'Profesor'], ['angka_kredit' => 300, 'pangkat' => 'Pembina Tk.I IV/d', 'golongan' => 'IV/d']);

        $pegawaiData = [
            ['nama' => 'Ahmad Fauzi', 'nik' => '198501152010011001', 'jk' => 'L', 'jabatan_idx' => 0, 'struktural_idx' => 0, 'fungsional_idx' => 2, 'anak' => 2, 'masa' => '2010-2027'],
            ['nama' => 'Rina Marlina', 'nik' => '198703222012012002', 'jk' => 'P', 'jabatan_idx' => 0, 'struktural_idx' => 1, 'fungsional_idx' => 2, 'anak' => 1, 'masa' => '2012-2029'],
            ['nama' => 'Dedi Kurniawan', 'nik' => '199005102015011003', 'jk' => 'L', 'jabatan_idx' => 0, 'struktural_idx' => 5, 'fungsional_idx' => 1, 'anak' => 0, 'masa' => '2015-2032'],
            ['nama' => 'Siti Nurhaliza', 'nik' => '199208182016012004', 'jk' => 'P', 'jabatan_idx' => 0, 'struktural_idx' => 6, 'fungsional_idx' => 1, 'anak' => 3, 'masa' => '2016-2033'],
            ['nama' => 'Budi Hartono', 'nik' => '198303052008011005', 'jk' => 'L', 'jabatan_idx' => 0, 'struktural_idx' => 3, 'fungsional_idx' => 3, 'anak' => 2, 'masa' => '2008-2025'],
            ['nama' => 'Dewi Anggraini', 'nik' => '199107122017012006', 'jk' => 'P', 'jabatan_idx' => 0, 'struktural_idx' => null, 'fungsional_idx' => 1, 'anak' => 0, 'masa' => '2017-2034'],
            ['nama' => 'Hendra Wijaya', 'nik' => '198801202013011007', 'jk' => 'L', 'jabatan_idx' => 1, 'struktural_idx' => null, 'fungsional_idx' => 0, 'anak' => 1, 'masa' => '2013-2030'],
            ['nama' => 'Yuniarti', 'nik' => '199305012018012008', 'jk' => 'P', 'jabatan_idx' => 1, 'struktural_idx' => null, 'fungsional_idx' => 0, 'anak' => 0, 'masa' => '2018-2035'],
            ['nama' => 'Rudi Santoso', 'nik' => '198609302011011009', 'jk' => 'L', 'jabatan_idx' => 2, 'struktural_idx' => null, 'fungsional_idx' => null, 'anak' => 2, 'masa' => '2011-2028'],
            ['nama' => 'Eka Putri', 'nik' => '199402142019012010', 'jk' => 'P', 'jabatan_idx' => 2, 'struktural_idx' => null, 'fungsional_idx' => null, 'anak' => 0, 'masa' => '2019-2036'],
            ['nama' => 'Andi Cahyono', 'nik' => '198904072014011011', 'jk' => 'L', 'jabatan_idx' => 3, 'struktural_idx' => null, 'fungsional_idx' => null, 'anak' => 1, 'masa' => '2014-2031'],
            ['nama' => 'Maya Sari', 'nik' => '199511282020012012', 'jk' => 'P', 'jabatan_idx' => 3, 'struktural_idx' => null, 'fungsional_idx' => null, 'anak' => 0, 'masa' => '2020-2037'],
            ['nama' => 'Hasan Basri', 'nik' => '198006102005011013', 'jk' => 'L', 'jabatan_idx' => 4, 'struktural_idx' => null, 'fungsional_idx' => null, 'anak' => 3, 'masa' => '2005-2022'],
            ['nama' => 'Nurul Hidayah', 'nik' => '199608052021012014', 'jk' => 'P', 'jabatan_idx' => 4, 'struktural_idx' => null, 'fungsional_idx' => null, 'anak' => 0, 'masa' => '2021-2038'],
        ];

        $today = Carbon::now();

        $rolePegawai = Role::where('name', 'pegawai')->first();
        $roleTendik = Role::where('name', 'tendik')->first();

        $createdPegawai = [];

        foreach ($pegawaiData as $idx => $data) {
            $username = strtolower(str_replace(' ', '.', $data['nama']));
            $user = User::firstOrCreate(
                ['username' => $username],
                [
                    'name' => $data['nama'],
                    'email' => $username.'@simppay.test',
                    'password' => Hash::make('password'),
                ]
            );

            $isDosen = in_array($data['jabatan_idx'], [0, 1]);
            $user->assignRole($isDosen ? 'pegawai' : 'tendik');

            $strukturalId = $data['struktural_idx'] !== null
                ? Struktural::all()->pluck('id')[$data['struktural_idx']] ?? null
                : null;

            $fungsionalId = $data['fungsional_idx'] !== null
                ? Fungsional::all()->pluck('id')[$data['fungsional_idx']] ?? null
                : null;

            $pegawai = Pegawai::firstOrCreate(
                ['nik' => $data['nik']],
                [
                    'user_id' => $user->id,
                    'jabatan_id' => $jabatans[$data['jabatan_idx']]->id,
                    'struktural_id' => $strukturalId,
                    'fungsional_id' => $fungsionalId,
                    'nama_pegawai' => $data['nama'],
                    'jenis_kelamin' => $data['jk'],
                    'status_pegawai' => 'aktif',
                    'tanggal_masuk' => Carbon::parse(explode('-', $data['masa'])[0].'-01-01'),
                    'agama' => 'Islam',
                    'status_kawin' => $data['anak'] > 0 ? 'kawin' : 'belum_kawin',
                    'masa_jabatan' => $data['masa'],
                ]
            );

            if ($data['anak'] > 0) {
                for ($a = 1; $a <= $data['anak']; $a++) {
                    PegawaiAnak::firstOrCreate(
                        [
                            'pegawai_id' => $pegawai->id,
                            'nama_anak' => 'Anak '.$data['nama'].' '.$a,
                        ],
                        [
                            'tempat_tanggal_lahir' => 'Bandung, '.Carbon::now()->subYears(rand(3, 18))->subMonths(rand(0, 11))->format('d-m-Y'),
                            'jenis_kelamin' => $a % 2 === 0 ? 'P' : 'L',
                            'anak_ke' => $a,
                        ]
                    );
                }
            }

            $createdPegawai[] = $pegawai;
        }

        $payrollDetail = null;

        for ($m = 5; $m >= 0; $m--) {
            $periode = Carbon::now()->subMonths($m)->startOfMonth();
            $run = PayrollRun::firstOrCreate(
                ['periode' => $periode],
                [
                    'status' => 'finalized',
                    'calculated_by' => $admin->id,
                    'calculated_at' => $periode->copy()->addDays(5),
                    'finalized_by' => $admin->id,
                    'finalized_at' => $periode->copy()->addDays(7),
                ]
            );

            foreach ($createdPegawai as $p) {
                $jabatan = $p->jabatan;
                $total = $jabatan->gaji_pokok + $jabatan->tj_transport + $jabatan->uang_makan;

                $detail = PayrollDetail::firstOrCreate(
                    [
                        'payroll_run_id' => $run->id,
                        'pegawai_id' => $p->id,
                    ],
                    [
                        'gaji_pokok' => $jabatan->gaji_pokok,
                        'tj_transport' => $jabatan->tj_transport,
                        'uang_makan' => $jabatan->uang_makan,
                        'potongan_alpha' => 0,
                        'total_tunjangan_tambahan' => 0,
                        'total_potongan_tambahan' => 0,
                        'honor_kelebihan_sks' => 0,
                        'total_gaji' => $total,
                    ]
                );

                if ($m === 0) {
                    $payrollDetail = $detail;
                }
            }

            $hadir = rand(20, 25);
            $sakit = rand(0, 1);
            $alpha = rand(0, 2);
            foreach ($createdPegawai as $p) {
                Kehadiran::firstOrCreate(
                    [
                        'pegawai_id' => $p->id,
                        'periode' => $periode,
                    ],
                    [
                        'hadir' => $hadir + rand(-1, 1),
                        'sakit' => $sakit,
                        'alpha' => $alpha,
                    ]
                );
            }
        }

        if ($payrollDetail) {
            $payrollDetail->update([
                'potongan_alpha' => 100000,
                'total_gaji' => $payrollDetail->total_gaji - 100000,
            ]);
        }

        PotonganGaji::firstOrCreate(['nama_potongan' => 'Iuran BPJS Kesehatan'], [
            'tipe' => 'persentase', 'nilai' => 1, 'is_alpha_penalty' => false, 'aktif' => true,
        ]);
        PotonganGaji::firstOrCreate(['nama_potongan' => 'Iuran BPJS Ketenagakerjaan'], [
            'tipe' => 'persentase', 'nilai' => 0.89, 'is_alpha_penalty' => false, 'aktif' => true,
        ]);
        PotonganGaji::firstOrCreate(['nama_potongan' => 'Iuran pensiun'], [
            'tipe' => 'persentase', 'nilai' => 2, 'is_alpha_penalty' => false, 'aktif' => true,
        ]);

        TunjanganGaji::firstOrCreate(['nama_tunjangan' => 'Tunjangan Anak'], [
            'target_tipe' => 'semua', 'nominal' => 200000, 'aktif' => true,
        ]);
        TunjanganGaji::firstOrCreate(['nama_tunjangan' => 'Tunjangan Kehadiran'], [
            'target_tipe' => 'semua', 'nominal' => 300000, 'aktif' => true,
        ]);
    }
}
