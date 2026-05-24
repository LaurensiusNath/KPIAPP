<?php

namespace Database\Seeders;

use App\Models\Appraisal;
use App\Models\Division;
use App\Models\Kpi;
use App\Models\KpiValue;
use App\Models\Period;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class DemoAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Set periode 2026 semester 1 sebagai periode aktif
            Period::query()->update(['is_active' => false]);
            $period = Period::firstOrCreate(
                [
                    'year' => 2026,
                    'semester' => 1,
                ],
                [
                    'is_active' => true,
                ]
            );

            // 2. Admin demo utama (tetap ada untuk login)
            User::updateOrCreate(
                ['email' => 'admin@demo.test'],
                [
                    'name' => 'Admin Demo',
                    'password' => Crypt::encryptString('password'),
                    'role' => 'admin',
                    'division_id' => null,
                    'email_verified_at' => now(),
                ]
            );

            // 3. Enam akun baru yang belum terhubung dengan divisi manapun untuk demo pembuatan divisi
            for ($i = 1; $i <= 6; $i++) {
                User::updateOrCreate(
                    ['email' => "unassigned{$i}@demo.test"],
                    [
                        'name' => "Akun Demo {$i}",
                        'password' => Crypt::encryptString('password'),
                        'role' => 'user',
                        'division_id' => null,
                        'email_verified_at' => now(),
                    ]
                );
            }

            // 4. Satu divisi demo dengan 1 team leader dan 5 staff yang proses KPI bulanannya sudah selesai
            $teamLeader = User::updateOrCreate(
                ['email' => 'leader-demo@kpiapp.test'],
                [
                    'name' => 'Team Leader Demo',
                    'password' => Crypt::encryptString('password'),
                    'role' => 'team-leader',
                    'division_id' => null, // diisi setelah divisi dibuat
                    'email_verified_at' => now(),
                ]
            );

            $division = Division::updateOrCreate(
                ['name' => 'Divisi Demo'],
                [
                    'leader_id' => $teamLeader->id,
                ]
            );

            // pastikan team leader terhubung ke divisi demo
            $teamLeader->update(['division_id' => $division->id]);

            $staffMembers = collect();
            for ($i = 1; $i <= 5; $i++) {
                $staffMembers->push(
                    User::updateOrCreate(
                        ['email' => "staff-demo{$i}@kpiapp.test"],
                        [
                            'name' => "Staff Divisi Demo {$i}",
                            'password' => Crypt::encryptString('password'),
                            'role' => 'user',
                            'division_id' => $division->id,
                            'email_verified_at' => now(),
                        ]
                    )
                );
            }

            // 5. KPI item untuk setiap staff (5 KPI per staff, bobot total 100)
            $kpiTemplates = [
                ['title' => 'Kualitas & Ketelitian Kerja', 'weight' => 25],
                ['title' => 'Pencapaian Target & Produktivitas', 'weight' => 25],
                ['title' => 'Kerjasama Tim & Komunikasi', 'weight' => 20],
                ['title' => 'Inisiatif & Penyelesaian Masalah', 'weight' => 15],
                ['title' => 'Disiplin & Manajemen Waktu', 'weight' => 15],
            ];

            $criteriaScale = [
                1 => 'Jauh di Bawah Harapan',
                2 => 'Di Bawah Harapan',
                3 => 'Sesuai Harapan',
                4 => 'Melebihi Harapan',
                5 => 'Jauh Melebihi Harapan',
            ];

            $notes = [
                'Hasil kerja konsisten dan sesuai standar.',
                'Menunjukkan peningkatan produktivitas bulan ini.',
                'Perlu meningkatkan komunikasi dengan rekan tim.',
                'Sangat proaktif dalam mencari solusi.',
                'Semua target bulanan berhasil dicapai.',
                'Kolaborasi yang sangat baik dalam proyek tim.',
                'Memberikan ide-ide inovatif yang bermanfaat.',
                'Selalu menyelesaikan tugas tepat waktu.',
            ];

            $staffMembers->each(function (User $staff) use ($period, $division, $teamLeader, $kpiTemplates, $criteriaScale, $notes) {
                foreach ($kpiTemplates as $template) {
                    $kpi = Kpi::create([
                        'user_id' => $staff->id,
                        'period_id' => $period->id,
                        'title' => $template['title'],
                        'weight' => $template['weight'],
                        'criteria_scale' => $criteriaScale,
                    ]);

                    // Buat KpiValue untuk bulan 1-5 (Januari - Mei 2026)
                    for ($month = 1; $month <= 5; $month++) {
                        KpiValue::create([
                            'kpi_id' => $kpi->id,
                            'user_id' => $staff->id,
                            'evaluator_id' => $teamLeader->id,
                            'division_id' => $division->id,
                            'period_id' => $period->id,
                            'month' => $month,
                            'score' => rand(3, 5),
                            'note' => $notes[array_rand($notes)],
                            'is_submitted' => true,
                        ]);
                    }
                }
            });

            // 6. Buat Appraisal untuk setiap staff dengan status 'pending_teamleader'
            $staffMembers->each(function (User $staff) use ($period, $division, $teamLeader) {
                Appraisal::create([
                    'user_id' => $staff->id,
                    'team_leader_id' => $teamLeader->id,
                    'division_id' => $division->id,
                    'period_id' => $period->id,
                    'final_score' => null,
                    'comment_teamleader' => null,
                    'comment_hrd' => null,
                    'status' => 'pending_teamleader',
                    'teamleader_submitted_at' => null,
                    'hrd_submitted_at' => null,
                ]);
            });
        });
    }
}
