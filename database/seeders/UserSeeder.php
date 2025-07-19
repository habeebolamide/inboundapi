<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // OWNER (Platform superuser — not tied to an org)
        User::create([
            'organization_id' => 1, 
            'name' => 'Platform Owner',
            'email' => 'owner@inbound.com',
            'password' => Hash::make('password'),
            'user_type_id' => 1,
            'user_id' => 'S123456722', // Example user ID
        ]);

        // ADMIN
        User::create([
            'organization_id' => 1,
            'name' => 'Dr. Ahmed (Admin)',
            'email' => 'admin@crescent.com',
            'password' => Hash::make('password'),
            'user_type_id' => 2,
            'user_id' => 'S123456711', // Example user ID
        ]);

        // SUPERVISOR
        User::create([
            'organization_id' => 1,
            'name' => 'Mrs. Zainab (Supervisor)',
            'email' => 'supervisor@crescent.com',
            'password' => Hash::make('password'),
            'user_type_id' => 3,
            'user_id' => 'S12345634', // Example user ID
        ]);

        // MEMBER
        User::create([
            'organization_id' => 1,
            'name' => 'Abdul Rahman (Member)',
            'email' => 'member@student.com',
            'password' => Hash::make('password'),
            'user_type_id' => 4,
            'user_id' => 'S123456789', // Example user ID
        ]);
    }
}
