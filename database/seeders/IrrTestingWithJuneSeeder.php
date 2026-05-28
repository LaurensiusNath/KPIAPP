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
 * IRR Testing Seeder — WITH JUNE DATA (for notebook testing).
 *
 * Identik dengan IrrTestingSeeder, KECUALI bulan 6 (Juni) sudah terisi
 * dengan skor yang mencerminkan tiga tingkat performa skenario:
 *
 *   Andi  (performa RENDAH)  → skor Juni cenderung 1–2
 *   Budi  (performa SEDANG)  → skor Juni cenderung 3
 *   Citra (performa TINGGI)  → skor Juni cenderung 4–5
 *
 * Skor antar rater (TL-1, TL-2, TL-3) dibuat sedikit bervariasi agar
 * ICC tidak sempurna 1.0 (tidak realistis), namun tetap menunjukkan
 * konsistensi yang baik — sehingga notebook dapat menghasilkan nilai
 * ICC yang bermakna untuk keperluan testing.
 *
 * Cara pakai:
 *   php artisan db:seed --class=IrrTestingWithJuneSeeder
 *
 * CATATAN: Seeder ini TIDAK menyentuh IrrTestingSeeder sama sekali.
 *          Jalankan migrate:fresh sebelum seeder ini jika ingin data bersih.
 */
class IrrTestingWithJuneSeeder extends Seeder
{
    /**
     * Anchor BARS untuk empat dimensi penilaian (subbab A.2 skripsi).
     * Identik dengan IrrTestingSeeder.
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

    /**
     * Skor bulan Juni (post-test) per skenario, per dimensi, per rater (kode divisi A/B/C).
     *
     * Desain skor:
     * - Andi (rendah)  : mayoritas 1–2, sesekali 2 agar tidak semua sama persis
     * - Budi  (sedang) : mayoritas 3, sesekali 2 atau 4
     * - Citra (tinggi) : mayoritas 4–5, sesekali 4 agar tidak semua 5
     *
     * Variasi kecil antar rater disengaja agar ICC < 1.0 (realistis).
     * Format: [skenario][dimensi][kode_divisi] = skor
     */
    private array $juneScores = [
        'Andi' => [
            'Kualitas Kerja'  => ['A' => 1, 'B' => 2, 'C' => 1],
            'Kedisiplinan'    => ['A' => 1, 'B' => 1, 'C' => 2],
            'Tanggung Jawab'  => ['A' => 2, 'B' => 1, 'C' => 2],
            'Kerja Sama Tim'  => ['A' => 1, 'B' => 2, 'C' => 1],
        ],
        'Budi' => [
            'Kualitas Kerja'  => ['A' => 3, 'B' => 3, 'C' => 4],
            'Kedisiplinan'    => ['A' => 4, 'B' => 3, 'C' => 3],
            'Tanggung Jawab'  => ['A' => 3, 'B' => 3, 'C' => 3],
            'Kerja Sama Tim'  => ['A' => 3, 'B' => 4, 'C' => 3],
        ],
        'Citra' => [
            'Kualitas Kerja'  => ['A' => 5, 'B' => 5, 'C' => 4],
            'Kedisiplinan'    => ['A' => 5, 'B' => 4, 'C' => 5],
            'Tanggung Jawab'  => ['A' => 5, 'B' => 5, 'C' => 5],
            'Kerja Sama Tim'  => ['A' => 4, 'B' => 5, 'C' => 5],
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
                    'leader'   => $leader,
                    'division' => $division,
                ];
            }

            // 4. Sembilan staf (3 skenario × 3 divisi).
            $scenarios = ['Andi', 'Budi', 'Citra'];

            $kpiTemplates = [
                ['title' => 'Kualitas Kerja',  'weight' => 25],
                ['title' => 'Kedisiplinan',    'weight' => 25],
                ['title' => 'Tanggung Jawab',  'weight' => 25],
                ['title' => 'Kerja Sama Tim',  'weight' => 25],
            ];

            foreach ($scenarios as $scenarioName) {
                foreach ($teamLeaders as $code => $tl) {
                    $emailLocal = strtolower($scenarioName) . '.' . strtolower($code);
                    $staff = User::updateOrCreate(
                        ['email' => "{$emailLocal}@bnp.test"],
                        [
                            'name'             => "{$scenarioName} - Divisi {$code}",
                            'password'         => Crypt::encryptString('password'),
                            'role'             => 'user',
                            'division_id'      => $tl['division']->id,
                            'email_verified_at' => now(),
                        ]
                    );

                    foreach ($kpiTemplates as $template) {
                        $dimensi = $template['title'];

                        $kpi = Kpi::updateOrCreate(
                            [
                                'user_id'   => $staff->id,
                                'period_id' => $period->id,
                                'title'     => $dimensi,
                            ],
                            [
                                'weight'         => $template['weight'],
                                'criteria_scale' => $this->bars[$dimensi],
                            ]
                        );

                        // Bersihkan value lama agar idempotent.
                        KpiValue::where('kpi_id', $kpi->id)->delete();

                        // Bulan 1–5: random 2–5 (data historis).
                        for ($month = 1; $month <= 5; $month++) {
                            KpiValue::create([
                                'kpi_id'       => $kpi->id,
                                'user_id'      => $staff->id,
                                'evaluator_id' => $tl['leader']->id,
                                'division_id'  => $tl['division']->id,
                                'period_id'    => $period->id,
                                'month'        => $month,
                                'score'        => rand(2, 5),
                                'note'         => "Data historis bulan {$month} (random).",
                                'is_submitted' => true,
                            ]);
                        }

                        // Bulan 6: skor post-test sesuai skenario & rater.
                        $juneScore = $this->juneScores[$scenarioName][$dimensi][$code];
                        KpiValue::create([
                            'kpi_id'       => $kpi->id,
                            'user_id'      => $staff->id,
                            'evaluator_id' => $tl['leader']->id,
                            'division_id'  => $tl['division']->id,
                            'period_id'    => $period->id,
                            'month'        => 6,
                            'score'        => $juneScore,
                            'note'         => "Skor post-test IRR bulan Juni (skenario: {$scenarioName}, rater: TL-{$code}).",
                            'is_submitted' => true,
                        ]);
                    }
                }
            }

            // Ringkasan output
            $this->command->info('✅ IRR testing data (WITH JUNE) seeded successfully!');
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
            $this->command->info('   - KpiValue : Jan-Mei random, Juni TERISI (post-test scores)');
            $this->command->info('   - Appraisal: tidak dibuat');
            $this->command->info('   - Password : "password" untuk semua akun');
            $this->command->info('');
            $this->command->info('📋 Skor Juni (post-test) yang digunakan:');
            $this->command->info('   Andi  (rendah) : KK=1/2/1, KD=1/1/2, TJ=2/1/2, KST=1/2/1');
            $this->command->info('   Budi  (sedang) : KK=3/3/4, KD=4/3/3, TJ=3/3/3, KST=3/4/3');
            $this->command->info('   Citra (tinggi) : KK=5/5/4, KD=5/4/5, TJ=5/5/5, KST=4/5/5');
            $this->command->info('   Format: TL-1/TL-2/TL-3 (Divisi A/B/C)');
        });
    }
}
