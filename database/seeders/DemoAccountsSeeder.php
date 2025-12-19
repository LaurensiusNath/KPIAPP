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
            // 1. Pastikan periode 2025 semester 2 ada dan aktif
            $period = Period::firstOrCreate(
                [
                    'year' => 2025,
                    'semester' => 2,
                ],
                [
                    'is_active' => true,
                ]
            );

            // 2. Admin demo utama (tetap ada untuk login)
            $admin = User::updateOrCreate(
                ['email' => 'admin@demo.test'],
                [
                    'name' => 'Admin Demo',
                    'password' => Crypt::encryptString('password'),
                    'role' => 'admin',
                    'division_id' => null,
                    'email_verified_at' => now(),
                ]
            );

            // 3. Enam akun baru yang belum terhubung dengan divisi manapun
            $unassignedUsers = collect();
            for ($i = 1; $i <= 6; $i++) {
                $unassignedUsers->push(
                    User::updateOrCreate(
                        ['email' => "unassigned{$i}@demo.test"],
                        [
                            'name' => "Akun Demo {$i}",
                            'password' => Crypt::encryptString('password'),
                            'role' => 'user',
                            'division_id' => null,
                            'email_verified_at' => now(),
                        ]
                    )
                );
            }

            // 4. Satu divisi demo dengan 1 team leader dan 5 staff
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
                ['name' => 'Divisi Demo KPI'],
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
                ['title' => 'Kualitas Pekerjaan', 'weight' => 25],
                ['title' => 'Produktivitas', 'weight' => 25],
                ['title' => 'Kolaborasi Tim', 'weight' => 20],
                ['title' => 'Inisiatif & Inovasi', 'weight' => 15],
                ['title' => 'Ketepatan Waktu', 'weight' => 15],
            ];

            $criteriaScale = [
                1 => 'Sangat Kurang',
                2 => 'Kurang',
                3 => 'Cukup',
                4 => 'Baik',
                5 => 'Sangat Baik',
            ];

            $notes = [
                'Pekerjaan diselesaikan dengan baik dan tepat waktu',
                'Menunjukkan peningkatan yang signifikan',
                'Perlu sedikit perbaikan dalam hal komunikasi',
                'Sangat proaktif dalam menyelesaikan masalah',
                'Target tercapai sesuai ekspektasi',
                'Kolaborasi dengan tim berjalan sangat baik',
                'Menunjukkan inisiatif yang bagus',
                'Konsisten dalam memberikan hasil berkualitas',
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

                    // KPI Values untuk bulan 7-11 (semester 2)
                    for ($month = 7; $month <= 11; $month++) {
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

            // 6. Appraisal untuk setiap staff pada periode ini
            $staffMembers->each(function (User $staff) use ($period, $division, $teamLeader) {
                $avgScore = KpiValue::where('user_id', $staff->id)
                    ->where('period_id', $period->id)
                    ->avg('score');

                Appraisal::create([
                    'user_id' => $staff->id,
                    'evaluator_id' => $teamLeader->id,
                    'division_id' => $division->id,
                    'period_id' => $period->id,
                    'final_score' => round($avgScore, 2),
                    'comment_teamleader' => 'Kinerja baik secara keseluruhan, sesuai dengan target divisi demo.',
                    'comment_hrd' => 'Data appraisal demo untuk keperluan presentasi dan testing.',
                    'is_finalized' => true,
                    'teamleader_submitted_at' => now()->subDays(5),
                    'hrd_submitted_at' => now()->subDays(3),
                ]);
            });
        });
    }
}
