<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = \App\Models\User::factory(10)->create();

        \App\Models\Ticket::factory(100)
            ->recycle($users)
            ->create();

        \App\Models\User::create([
            'email' => 'manager@manager.com',
            'password' => bcrypt('password'),
            'name' => 'The Manager',
            'is_manager' => true,
        ]);
    }
}
