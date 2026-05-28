<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\Kpi;
use App\Models\KpiValue;
use App\Models\Period;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/**
 * IRR (Inter-Rater Reliability) Testing Seeder.
 *
 * Karena pada sistem 1 staff hanya bisa berada di 1 divisi (dan 1 divisi
 * dipimpin 1 Team Leader), supaya KETIGA Team Leader bisa menilai
 * SKENARIO yang sama (Andi, Budi, Citra), dibuat 3x replika staf:
 *
 *   Divisi A (TL 1) : Andi-A,  Budi-A,  Citra-A
 *   Divisi B (TL 2) : Andi-B,  Budi-B,  Citra-B
 *   Divisi C (TL 3) : Andi-C,  Budi-C,  Citra-C
 *
 * Total: 1 admin + 3 TL + 9 staf. Semua staf untuk skenario yang sama
 * (mis. Andi-A/B/C) memakai indikator KPI dan anchor BARS yang IDENTIK.
 *
 * Period aktif : Semester 1, 2026 (Januari - Juni)
 * KPI Values   : bulan 1-5 di-random sebagai data historis,
 *                bulan 6 (Juni) DIKOSONGKAN untuk pengetesan IRR.
 * Appraisal    : tidak dibuat (sesuai permintaan).
 *
 * Cara pakai:
 *   php artisan db:seed --class=IrrTestingSeeder
 */
class IrrTestingSeeder extends Seeder
{
    /**
     * Anchor BARS untuk empat dimensi penilaian (subbab A.2 skripsi).
     */
    private array $bars = [
        'Kualitas Kerja' => [
            1 => 'Hasil kerja sering mengandung kesalahan kritis, sering harus dikerjakan ulang oleh rekan atau atasan, dan tidak memenuhi standar mutu yang ditetapkan.',
            2 => 'Hasil kerja kerap memerlukan revisi dari atasan untuk mencapai standar minimum, dengan kesalahan kecil yang berulang pada beberapa tugas.',
            3 => 'Hasil kerja umumnya memenuhi standar yang ditetapkan dengan revisi kecil sesekali, dan menyelesaikan tugas sesuai instruksi tanpa kesalahan signifikan.',
            4 => 'Hasil kerja konsisten memenuhi standar mutu, jarang membutuhkan revisi, dan menunjukkan ketelitian yang baik pada detail pekerjaan.',
            5 => 'Hasil kerja melampaui standar mutu yang ditetapkan, bebas dari kesalahan, dan menjadi acuan kualitas bagi rekan kerja lainnya.',
        ],
        'Kedisiplinan' => [
            1 => 'Sering terlambat, sering tidak hadir tanpa keterangan, dan kerap mengabaikan aturan kerja yang berlaku di divisi.',
            2 => 'Beberapa kali terlambat dalam sebulan, sesekali absen tanpa pemberitahuan tepat waktu, dan kurang konsisten mengikuti prosedur kerja.',
            3 => 'Hadir tepat waktu mayoritas hari kerja, mengikuti aturan dan prosedur kerja yang berlaku dengan penyesuaian sesekali.',
            4 => 'Konsisten hadir tepat waktu, patuh terhadap aturan dan prosedur, serta mengomunikasikan ketidakhadiran sebelumnya bila terjadi.',
            5 => 'Selalu hadir tepat waktu, menjadi contoh kedisiplinan bagi rekan kerja, dan menunjukkan komitmen tinggi terhadap aturan organisasi.',
        ],
        'Tanggung Jawab' => [
            1 => 'Sering meninggalkan tugas yang menjadi tanggung jawabnya, menyalahkan pihak lain saat terjadi kesalahan, dan tidak menyelesaikan pekerjaan hingga tuntas.',
            2 => 'Kerap menunda penyelesaian tugas yang menjadi tanggung jawabnya, perlu diingatkan berulang oleh atasan untuk menyelesaikan pekerjaan.',
            3 => 'Menyelesaikan tugas sesuai dengan tenggat yang diberikan dan menerima konsekuensi atas pekerjaan yang menjadi tanggung jawabnya.',
            4 => 'Menyelesaikan seluruh tugas tepat waktu, mengakui dan memperbaiki kesalahan tanpa diminta, serta proaktif memberikan progres pekerjaan.',
            5 => 'Menjalankan tanggung jawab melebihi ekspektasi, mengambil inisiatif menyelesaikan masalah di luar tugas pokok, dan menjadi penanggung jawab yang dapat diandalkan dalam situasi kritis.',
        ],
        'Kerja Sama Tim' => [
            1 => 'Cenderung bekerja sendiri, sulit diajak berkoordinasi, dan kerap memicu konflik di dalam tim.',
            2 => 'Bersedia bekerja sama hanya bila diminta, kontribusi terhadap diskusi tim minim, kadang menghambat alur kerja kolektif.',
            3 => 'Berpartisipasi dalam aktivitas tim sesuai peran yang diberikan dan menjaga komunikasi dasar dengan rekan kerja.',
            4 => 'Aktif berkontribusi pada diskusi tim, membantu rekan yang mengalami kesulitan, dan menjaga komunikasi yang konstruktif.',
            5 => 'Menjadi penggerak kolaborasi tim, secara konsisten membantu rekan kerja, dan mampu menjembatani perbedaan pendapat untuk mencapai tujuan bersama.',
        ],
    ];

    public function run(): void
    {
        DB::transaction(function () {
            // 1. Period aktif: Semester 1, 2026
            Period::query()->update(['is_active' => false]);
            $period = Period::firstOrCreate(
                ['year' => 2026, 'semester' => 1],
                ['is_active' => true]
            );
            $period->update(['is_active' => true]);

            // 2. Admin
            User::updateOrCreate(
                ['email' => 'admin@bnp.test'],
                [
                    'name' => 'Admin BNP',
                    'password' => Crypt::encryptString('password'),
                    'role' => 'admin',
                    'division_id' => null,
                    'email_verified_at' => now(),
                ]
            );

            // 3. Tiga Team Leader, masing-masing memimpin satu divisi.
            //    Divisi diberi nama netral A/B/C agar fokus pada penilaian.
            $tlSpecs = [
                ['code' => 'A', 'name' => 'Team Leader 1', 'email' => 'tl1@bnp.test', 'division' => 'Divisi A'],
                ['code' => 'B', 'name' => 'Team Leader 2', 'email' => 'tl2@bnp.test', 'division' => 'Divisi B'],
                ['code' => 'C', 'name' => 'Team Leader 3', 'email' => 'tl3@bnp.test', 'division' => 'Divisi C'],
            ];

            $teamLeaders = [];
            foreach ($tlSpecs as $tl) {
                $leader = User::updateOrCreate(
                    ['email' => $tl['email']],
                    [
                        'name' => $tl['name'],
                        'password' => Crypt::encryptString('password'),
                        'role' => 'team-leader',
                        'division_id' => null,
                        'email_verified_at' => now(),
                    ]
                );

                $division = Division::updateOrCreate(
                    ['name' => $tl['division']],
                    ['leader_id' => $leader->id]
                );

                $leader->update(['division_id' => $division->id]);

                $teamLeaders[$tl['code']] = [
                    'leader' => $leader,
                    'division' => $division,
                ];
            }

            // 4. Tiga skenario, masing-masing direplikasi ke 3 divisi.
            //    Suffix " - Divisi X" agar nama tetap unik per record tetapi
            //    tetap kelihatan sebagai skenario yang sama.
            $scenarios = ['Andi', 'Budi', 'Citra'];

            $kpiTemplates = [
                ['title' => 'Kualitas Kerja',  'weight' => 25],
                ['title' => 'Kedisiplinan',    'weight' => 25],
                ['title' => 'Tanggung Jawab',  'weight' => 25],
                ['title' => 'Kerja Sama Tim',  'weight' => 25],
            ];

            foreach ($scenarios as $scenarioName) {
                foreach ($teamLeaders as $code => $tl) {
                    $emailLocal = strtolower($scenarioName) . '.' . strtolower($code); // andi.a, budi.b, dst
                    $staff = User::updateOrCreate(
                        ['email' => "{$emailLocal}@bnp.test"],
                        [
                            'name' => "{$scenarioName}",
                            'password' => Crypt::encryptString('password'),
                            'role' => 'user',
                            'division_id' => $tl['division']->id,
                            'email_verified_at' => now(),
                        ]
                    );

                    foreach ($kpiTemplates as $template) {
                        $kpi = Kpi::updateOrCreate(
                            [
                                'user_id' => $staff->id,
                                'period_id' => $period->id,
                                'title' => $template['title'],
                            ],
                            [
                                'weight' => $template['weight'],
                                'criteria_scale' => $this->bars[$template['title']],
                            ]
                        );

                        // Bersihkan value lama untuk KPI ini supaya idempotent.
                        KpiValue::where('kpi_id', $kpi->id)->delete();

                        // Bulan 1..5 random 2..5; bulan 6 (Juni) sengaja kosong.
                        for ($month = 1; $month <= 5; $month++) {
                            KpiValue::create([
                                'kpi_id'       => $kpi->id,
                                'user_id'      => $staff->id,
                                'evaluator_id' => $tl['leader']->id,
                                'division_id'  => $tl['division']->id,
                                'period_id'    => $period->id,
                                'month'        => $month,
                                'score'        => rand(2, 5),
                                'note'         => "Data historis bulan {$month} (random untuk testing IRR).",
                                'is_submitted' => true,
                            ]);
                        }
                    }
                }
            }

            $this->command->info('✅ IRR testing data seeded successfully!');
            $this->command->info('📊 Summary:');
            $this->command->info('   - Period   : Semester 1, 2026 (Active)');
            $this->command->info('   - Admin    : 1 (admin@bnp.test)');
            $this->command->info('   - Team Lead: 3 (tl1@bnp.test, tl2@bnp.test, tl3@bnp.test)');
            $this->command->info('   - Divisi   : 3 (Divisi A, Divisi B, Divisi C)');
            $this->command->info('   - Staff    : 9 (3 skenario x 3 divisi)');
            $this->command->info('     · Andi  : andi.a@bnp.test, andi.b@bnp.test, andi.c@bnp.test');
            $this->command->info('     · Budi  : budi.a@bnp.test, budi.b@bnp.test, budi.c@bnp.test');
            $this->command->info('     · Citra : citra.a@bnp.test, citra.b@bnp.test, citra.c@bnp.test');
            $this->command->info('   - KPI      : 4 indikator x 9 staff (bobot 25% per indikator)');
            $this->command->info('   - KpiValue : Jan-Mei terisi random, Juni KOSONG (siap untuk IRR)');
            $this->command->info('   - Appraisal: tidak dibuat (sesuai permintaan)');
            $this->command->info('   - Password : "password" untuk semua akun');
        });
    }
}
