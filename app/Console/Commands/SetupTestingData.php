<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class SetupTestingData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kpi:setup-testing {--fresh : Fresh migrate database before seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup complete testing data for KPI App (26 users, KPIs, values, appraisals)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Setting up KPI App testing data...');
        $this->newLine();

        if ($this->option('fresh')) {
            if ($this->confirm('This will DROP ALL DATA. Are you sure?', false)) {
                $this->warn('⚠️  Dropping all tables and migrating fresh...');
                Artisan::call('migrate:fresh');
                $this->info('✅ Database migrated fresh');
            } else {
                $this->error('Aborted.');
                return 1;
            }
        }

        $this->info('🌱 Seeding testing data...');
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\TestingSeeder']);

        $this->newLine();
        $this->info('✨ Testing data setup complete!');
        $this->newLine();

        $this->table(
            ['Role', 'Email', 'Password'],
            [
                ['Admin', 'admin@kpiapp.test', 'password'],
                ['Team Leader 1', 'tl1@kpiapp.test', 'password'],
                ['Team Leader 2', 'tl2@kpiapp.test', 'password'],
                ['Team Leader 3', 'tl3@kpiapp.test', 'password'],
                ['Team Leader 4', 'tl4@kpiapp.test', 'password'],
                ['Team Leader 5', 'tl5@kpiapp.test', 'password'],
                ['Staff 1-20', 'staff1@kpiapp.test - staff20@kpiapp.test', 'password'],
            ]
        );

        $this->newLine();
        $this->info('📝 Quick Stats:');
        $this->line('   Total Users: 26 (1 admin, 5 team leaders, 20 staff)');
        $this->line('   Divisions: 5');
        $this->line('   Period: Semester 2, 2025 (Active)');
        $this->line('   KPI Data: Months 7-11 (July-November)');
        $this->newLine();

        return 0;
    }
}
