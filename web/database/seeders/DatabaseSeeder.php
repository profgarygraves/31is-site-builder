<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ReservedSubdomainSeeder::class,
        ]);

        if (app()->environment('local')) {
            User::factory()->create([
                'name' => 'Test Student',
                'email' => 'student@example.com',
            ]);
        }
    }
}
