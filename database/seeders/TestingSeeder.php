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

class TestingSeeder extends Seeder
{
    /**
     * Run the database seeds for comprehensive testing.
     * Creates: 5 divisions, 1 admin, 5 team leaders, 20 staff (4 per division)
     * Total: 26 users
     */
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Create Active Period (Semester 2, 2025)
            $period = Period::create([
                'year' => 2025,
                'semester' => 2,
                'is_active' => true,
            ]);

            // 2. Create Admin first
            $admin = User::create([
                'name' => 'Admin Sistem',
                'email' => 'admin@kpiapp.test',
                'password' => Crypt::encryptString('password'),
                'role' => 'admin',
                'division_id' => null,
                'email_verified_at' => now(),
            ]);

            // 3. Create Divisions with Team Leaders
            $divisionNames = [
                'IT & Technology',
                'Marketing & Sales',
                'Human Resources',
                'Finance & Accounting',
                'Operations & Logistics'
            ];

            $divisions = collect();
            $teamLeaders = collect();

            foreach ($divisionNames as $index => $divisionName) {
                // Create team leader first (without division_id)
                $teamLeader = User::create([
                    'name' => 'Team Leader ' . $divisionName,
                    'email' => 'tl' . ($index + 1) . '@kpiapp.test',
                    'password' => Crypt::encryptString('password'),
                    'role' => 'team-leader',
                    'division_id' => null, // Will be updated after division created
                    'email_verified_at' => now(),
                ]);

                // Create division with leader_id
                $division = Division::create([
                    'name' => $divisionName,
                    'leader_id' => $teamLeader->id,
                ]);

                // Update team leader with division_id
                $teamLeader->update(['division_id' => $division->id]);

                $divisions->push($division);
                $teamLeaders->push($teamLeader);
            }

            // 4. Create Staff (4 per division = 20 total)
            $staffMembers = collect();
            $divisions->each(function ($division, $divIndex) use (&$staffMembers) {
                for ($i = 1; $i <= 4; $i++) {
                    $staff = User::create([
                        'name' => 'Staff ' . $division->name . ' ' . $i,
                        'email' => 'staff' . ($divIndex * 4 + $i) . '@kpiapp.test',
                        'password' => Crypt::encryptString('password'),
                        'role' => 'user',
                        'division_id' => $division->id,
                        'email_verified_at' => now(),
                    ]);
                    $staffMembers->push($staff);
                }
            });

            // 5. Create KPIs for all staff (5 KPIs per staff)
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

            $staffMembers->each(function ($staff) use ($period, $kpiTemplates, $criteriaScale, $teamLeaders) {
                $teamLeader = $teamLeaders->firstWhere('division_id', $staff->division_id);

                foreach ($kpiTemplates as $template) {
                    $kpi = Kpi::create([
                        'user_id' => $staff->id,
                        'period_id' => $period->id,
                        'title' => $template['title'],
                        'weight' => $template['weight'],
                        'criteria_scale' => $criteriaScale,
                    ]);

                    // 6. Create KPI Values for months 7-11 (Semester 2: July-November)
                    for ($month = 7; $month <= 11; $month++) {
                        KpiValue::create([
                            'kpi_id' => $kpi->id,
                            'user_id' => $staff->id,
                            'evaluator_id' => $teamLeader->id,
                            'division_id' => $staff->division_id,
                            'period_id' => $period->id,
                            'month' => $month,
                            'score' => rand(3, 5), // Random score 3-5 for realistic data
                            'note' => $this->generateRandomNote(),
                            'is_submitted' => true,
                        ]);
                    }
                }
            });

            // 7. Create Appraisals for all staff
            $staffMembers->each(function ($staff, $index) use ($period, $teamLeaders, $admin) {
                $teamLeader = $teamLeaders->firstWhere('division_id', $staff->division_id);

                // Calculate average score from KPI values
                $avgScore = KpiValue::where('user_id', $staff->id)
                    ->where('period_id', $period->id)
                    ->avg('score');

                // Vary appraisal status for testing different scenarios
                $status = $index % 4;

                $appraisal = Appraisal::create([
                    'user_id' => $staff->id,
                    'evaluator_id' => $teamLeader->id,
                    'division_id' => $staff->division_id,
                    'period_id' => $period->id,
                    'final_score' => round($avgScore, 2),
                    'comment_teamleader' => $status >= 1 ? 'Kinerja yang baik secara keseluruhan. Terus pertahankan dan tingkatkan.' : null,
                    'comment_hrd' => $status >= 3 ? 'Direkomendasikan untuk program pengembangan karir.' : null,
                    'is_finalized' => $status === 3,
                    'teamleader_submitted_at' => $status >= 1 ? now()->subDays(rand(1, 10)) : null,
                    'hrd_submitted_at' => $status >= 3 ? now()->subDays(rand(1, 5)) : null,
                ]);
            });

            $this->command->info('✅ Testing data seeded successfully!');
            $this->command->info('📊 Summary:');
            $this->command->info('   - Divisions: 5');
            $this->command->info('   - Admin: 1 (admin@kpiapp.test)');
            $this->command->info('   - Team Leaders: 5 (tl1@kpiapp.test - tl5@kpiapp.test)');
            $this->command->info('   - Staff: 20 (staff1@kpiapp.test - staff20@kpiapp.test)');
            $this->command->info('   - Total Users: 26');
            $this->command->info('   - KPIs: ' . Kpi::count());
            $this->command->info('   - KPI Values: ' . KpiValue::count());
            $this->command->info('   - Appraisals: ' . Appraisal::count());
            $this->command->info('   - Period: Semester 2, 2025 (Active)');
            $this->command->info('   - Password for all: password');
        });
    }

    /**
     * Generate random note for KPI value
     */
    private function generateRandomNote(): string
    {
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

        return $notes[array_rand($notes)];
    }
}
