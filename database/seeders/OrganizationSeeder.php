<?php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         Organization::create([
            'name' => 'Crescent University',
            'slug' => Str::slug('Crescent University'),
            'email' => 'info@crescent.edu.ng',
            'address' => 'Kobape Road, Abeokuta',
            'type' => 'school',
        ]);

        Organization::create([
            'name' => 'TechWave Inc.',
            'slug' => Str::slug('TechWave Inc.'),
            'email' => 'hello@techwave.com',
            'address' => '15 Admiralty Way, Lekki Phase 1, Lagos',
            'type' => 'company',
        ]);
    }
}
