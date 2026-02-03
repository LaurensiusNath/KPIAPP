<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Division;
use App\Models\Period;
use App\Models\Kpi;
use App\Models\KpiValue;
use App\Models\Appraisal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class DemoTestingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Encrypt password once for all users
            $encryptedPassword = Crypt::encryptString('password');

            // ============================================
            // 1. CREATE USERS WITHOUT DIVISION (to avoid circular dependency)
            // ============================================

            // Admin
            $admin = User::create([
                'name' => 'Administrator',
                'email' => 'admin@demo.test',
                'password' => $encryptedPassword,
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);

            // Special Users (biasa, bukan team leader)
            $teamLeaderLama = User::create([
                'name' => 'Team Leader Lama',
                'email' => 'teamleaderlama@email.com',
                'password' => $encryptedPassword,
                'role' => 'user',
                'email_verified_at' => now(),
            ]);

            $teamLeaderBaru = User::create([
                'name' => 'Team Leader Baru',
                'email' => 'teamleaderbaru@email.com',
                'password' => $encryptedPassword,
                'role' => 'user',
                'email_verified_at' => now(),
            ]);

            // Leaders for Divisions (role: team-leader)
            $leaderDemo1 = User::create([
                'name' => 'Leader Divisi Demo 1',
                'email' => 'leader-demo1@kpiapp.test',
                'password' => $encryptedPassword,
                'role' => 'team-leader',
                'email_verified_at' => now(),
            ]);

            $leaderDemo2 = User::create([
                'name' => 'Leader Divisi Demo 2',
                'email' => 'leader-demo2@kpiapp.test',
                'password' => $encryptedPassword,
                'role' => 'team-leader',
                'email_verified_at' => now(),
            ]);

            // Staff for Divisi Demo 1 (5 staff)
            $staffDemo1 = [];
            for ($i = 1; $i <= 5; $i++) {
                $staffDemo1[$i] = User::create([
                    'name' => "Staff Divisi Demo1 {$i}",
                    'email' => "staff-demo1-{$i}@kpiapp.test",
                    'password' => $encryptedPassword,
                    'role' => 'user',
                    'email_verified_at' => now(),
                ]);
            }

            // Staff for Divisi Demo 2 (5 staff)
            $staffDemo2 = [];
            for ($i = 1; $i <= 5; $i++) {
                $staffDemo2[$i] = User::create([
                    'name' => "Staff Divisi Demo2 {$i}",
                    'email' => "staff-demo2-{$i}@kpiapp.test",
                    'password' => $encryptedPassword,
                    'role' => 'user',
                    'email_verified_at' => now(),
                ]);
            }

            // ============================================
            // 2. CREATE DIVISIONS
            // ============================================

            $divisionDemo1 = Division::create([
                'name' => 'Divisi Demo 1',
                'leader_id' => $leaderDemo1->id,
            ]);

            $divisionDemo2 = Division::create([
                'name' => 'Divisi Demo 2',
                'leader_id' => $leaderDemo2->id,
            ]);

            // ============================================
            // 3. UPDATE USERS WITH DIVISION_ID
            // ============================================

            $leaderDemo1->update(['division_id' => $divisionDemo1->id]);
            $leaderDemo2->update(['division_id' => $divisionDemo2->id]);

            foreach ($staffDemo1 as $staff) {
                $staff->update(['division_id' => $divisionDemo1->id]);
            }

            foreach ($staffDemo2 as $staff) {
                $staff->update(['division_id' => $divisionDemo2->id]);
            }

            // ============================================
            // 4. CREATE ACTIVE PERIOD (Semester 1 2026)
            // ============================================

            $period = Period::create([
                'year' => 2026,
                'semester' => 1,
                'is_active' => true,
            ]);

            // ============================================
            // 5. CREATE KPIs & EVALUATIONS
            // ============================================

            $months = [1, 2, 3, 4, 5, 6]; // Semester 1

            // ===========================================
            // DIVISI DEMO 1 - All Staff Complete Process
            // ===========================================

            foreach ($staffDemo1 as $index => $staff) {
                // Create 5 KPIs (20% each = 100%)
                $kpis = $this->createKpisForUser($staff, $period, $leaderDemo1);

                if ($index === 1) {
                    // Staff Divisi Demo1 1 - SPECIAL CASE
                    // Monthly evaluations complete, appraisal only TL submitted
                    $this->createMonthlyEvaluations($kpis, $staff, $leaderDemo1, $period, $divisionDemo1, $months);
                    $this->createAppraisalTLOnly($staff, $leaderDemo1, $period, $divisionDemo1, $kpis, $months);
                } else {
                    // Normal: Full process
                    $this->createMonthlyEvaluations($kpis, $staff, $leaderDemo1, $period, $divisionDemo1, $months);
                    $this->createAppraisalComplete($staff, $leaderDemo1, $period, $divisionDemo1, $kpis, $months);
                }
            }

            // ===========================================
            // DIVISI DEMO 2
            // ===========================================

            foreach ($staffDemo2 as $index => $staff) {
                if ($index === 1) {
                    // Staff Divisi Demo2 1 - SPECIAL CASE
                    // No KPIs, no evaluations, no appraisal
                    continue;
                }

                // Create KPIs for all others
                $kpis = $this->createKpisForUser($staff, $period, $leaderDemo2);

                if ($index === 2) {
                    // Staff Divisi Demo2 2 - SPECIAL CASE
                    // Monthly evaluations complete, NO appraisal at all
                    $this->createMonthlyEvaluations($kpis, $staff, $leaderDemo2, $period, $divisionDemo2, $months);
                } else {
                    // Normal: Full process
                    $this->createMonthlyEvaluations($kpis, $staff, $leaderDemo2, $period, $divisionDemo2, $months);
                    $this->createAppraisalComplete($staff, $leaderDemo2, $period, $divisionDemo2, $kpis, $months);
                }
            }
        });

        $this->command->info('✅ Demo Testing Seeder completed successfully!');
    }

    /**
     * Create 5 KPIs for a user with varied titles and 20% weight each
     */
    private function createKpisForUser(User $user, Period $period, User $evaluator): array
    {
        $kpiTitles = [
            'Pencapaian Target Penjualan',
            'Kualitas Layanan Pelanggan',
            'Efisiensi Operasional',
            'Inovasi dan Pengembangan',
            'Kolaborasi Tim',
        ];

        $kpis = [];
        foreach ($kpiTitles as $title) {
            $kpis[] = Kpi::create([
                'user_id' => $user->id,
                'period_id' => $period->id,
                'title' => $title,
                'weight' => 20.00,
                'criteria_scale' => [
                    '1' => 'Sangat Kurang - Tidak memenuhi ekspektasi',
                    '2' => 'Kurang - Perlu banyak perbaikan',
                    '3' => 'Cukup - Memenuhi standar minimal',
                    '4' => 'Baik - Melebihi ekspektasi',
                    '5' => 'Sangat Baik - Jauh melebihi ekspektasi',
                ],
            ]);
        }

        return $kpis;
    }

    /**
     * Create monthly evaluations for all 6 months (submitted)
     */
    private function createMonthlyEvaluations(array $kpis, User $user, User $evaluator, Period $period, Division $division, array $months): void
    {
        foreach ($months as $month) {
            foreach ($kpis as $kpi) {
                // Random score 3-5 for realistic data
                $score = rand(3, 5);

                KpiValue::create([
                    'kpi_id' => $kpi->id,
                    'user_id' => $user->id,
                    'evaluator_id' => $evaluator->id,
                    'division_id' => $division->id,
                    'period_id' => $period->id,
                    'month' => $month,
                    'score' => $score,
                    'note' => $this->generateNote($score),
                    'is_submitted' => true,
                ]);
            }
        }
    }

    /**
     * Create appraisal - Team Leader only submitted (for HRD testing)
     */
    private function createAppraisalTLOnly(User $user, User $evaluator, Period $period, Division $division, array $kpis, array $months): void
    {
        $finalScore = $this->calculateFinalScore($user, $period, $months);

        Appraisal::create([
            'user_id' => $user->id,
            'evaluator_id' => $evaluator->id,
            'division_id' => $division->id,
            'period_id' => $period->id,
            'final_score' => $finalScore,
            'comment_teamleader' => 'Kinerja konsisten baik sepanjang semester. Perlu dipertahankan dan ditingkatkan untuk semester berikutnya.',
            'comment_hrd' => null,
            'is_finalized' => false,
            'teamleader_submitted_at' => now()->subDays(5),
            'hrd_submitted_at' => null,
        ]);
    }

    /**
     * Create complete appraisal (TL + HRD submitted, finalized)
     */
    private function createAppraisalComplete(User $user, User $evaluator, Period $period, Division $division, array $kpis, array $months): void
    {
        $finalScore = $this->calculateFinalScore($user, $period, $months);

        Appraisal::create([
            'user_id' => $user->id,
            'evaluator_id' => $evaluator->id,
            'division_id' => $division->id,
            'period_id' => $period->id,
            'final_score' => $finalScore,
            'comment_teamleader' => 'Pencapaian KPI sangat memuaskan. Target tercapai dengan konsisten di semua aspek.',
            'comment_hrd' => 'Disetujui. Kinerja telah memenuhi standar perusahaan dan layak mendapat apresiasi.',
            'is_finalized' => true,
            'teamleader_submitted_at' => now()->subDays(10),
            'hrd_submitted_at' => now()->subDays(3),
        ]);
    }

    /**
     * Calculate final score from 6-month average
     */
    private function calculateFinalScore(User $user, Period $period, array $months): float
    {
        $monthlyAverages = [];

        foreach ($months as $month) {
            $kpiValues = KpiValue::query()
                ->join('kpis', 'kpi_values.kpi_id', '=', 'kpis.id')
                ->where('kpi_values.user_id', $user->id)
                ->where('kpi_values.period_id', $period->id)
                ->where('kpi_values.month', $month)
                ->where('kpi_values.is_submitted', true)
                ->select('kpi_values.score', 'kpis.weight')
                ->get();

            if ($kpiValues->isNotEmpty()) {
                $totalWeightedScore = 0;
                $totalWeight = 0;
                foreach ($kpiValues as $value) {
                    $totalWeightedScore += $value->score * $value->weight;
                    $totalWeight += $value->weight;
                }
                $monthlyAverages[] = $totalWeight > 0 ? $totalWeightedScore / $totalWeight : 0;
            }
        }

        if (empty($monthlyAverages)) {
            return 0;
        }

        return round(array_sum($monthlyAverages) / count($monthlyAverages), 2);
    }

    /**
     * Generate note based on score
     */
    private function generateNote(int $score): string
    {
        $notes = [
            1 => 'Perlu peningkatan signifikan',
            2 => 'Ada beberapa area yang perlu diperbaiki',
            3 => 'Memenuhi standar yang ditetapkan',
            4 => 'Kinerja di atas ekspektasi',
            5 => 'Kinerja luar biasa, melebihi target',
        ];

        return $notes[$score] ?? 'Catatan evaluasi';
    }
}
