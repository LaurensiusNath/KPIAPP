<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(5)->create();
        // User::factory()->create([
        //     'name' => 'Admin User',
        //     'email' => 'admin@email.com',
        //     'password' => Crypt::encryptString('admin123'),
        //     'role' => 'admin',
        // ]);

        // $this->call([UserSeeder::class]);
    }
}
