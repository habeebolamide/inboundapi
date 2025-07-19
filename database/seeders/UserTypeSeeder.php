<?php

namespace Database\Seeders;

use App\Models\UserType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       UserType::insert([
            ['id'=>1,'name' => 'owner'],
            ['id'=>2,'name' => 'admin'],
            ['id'=>3,'name' => 'supervisor'],
            ['id'=>4,'name' => 'member'],
        ]);
    }
}
