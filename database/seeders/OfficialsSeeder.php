<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OfficialsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('officials')->insert([

            // =========================
            // BARANGAY CAPTAIN
            // =========================
            [
                'barangay' => 'Punta',
                'full_name' => 'Juan Dela Cruz',
                'gender' => 'Male',
                'position' => 'Barangay Captain',
                'committee' => null,
                'address' => 'Punta, Carles',
                'contact_number' => '09120000001',
                'email' => 'captain.punta@example.com',
                'term_start' => Carbon::parse('2023-01-01'),
                'term_end' => Carbon::parse('2026-12-31'),
                'status' => 'active',
                'photo' => null,
                'remarks' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // =========================
            // SECRETARY
            // =========================
            [
                'barangay' => 'Punta',
                'full_name' => 'Maria Santos',
                'gender' => 'Female',
                'position' => 'Barangay Secretary',
                'committee' => null,
                'address' => 'Punta, Carles',
                'contact_number' => '09120000002',
                'email' => 'secretary.punta@example.com',
                'term_start' => Carbon::parse('2023-01-01'),
                'term_end' => Carbon::parse('2026-12-31'),
                'status' => 'active',
                'photo' => null,
                'remarks' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // =========================
            // TREASURER
            // =========================
            [
                'barangay' => 'Punta',
                'full_name' => 'Pedro Reyes',
                'gender' => 'Male',
                'position' => 'Barangay Treasurer',
                'committee' => null,
                'address' => 'Punta, Carles',
                'contact_number' => '09120000003',
                'email' => 'treasurer.punta@example.com',
                'term_start' => Carbon::parse('2023-01-01'),
                'term_end' => Carbon::parse('2026-12-31'),
                'status' => 'active',
                'photo' => null,
                'remarks' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // =========================
            // 8 KAGAWADS
            // =========================
            [
                'barangay' => 'Punta',
                'full_name' => 'Ana Lopez',
                'gender' => 'Female',
                'position' => 'Kagawad',
                'committee' => 'Peace and Order',
                'address' => 'Punta, Carles',
                'contact_number' => '09120000004',
                'email' => 'kagawad1@example.com',
                'term_start' => Carbon::parse('2023-01-01'),
                'term_end' => Carbon::parse('2026-12-31'),
                'status' => 'active',
                'photo' => null,
                'remarks' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'barangay' => 'Punta',
                'full_name' => 'Jose Lim',
                'gender' => 'Male',
                'position' => 'Kagawad',
                'committee' => 'Health',
                'address' => 'Punta, Carles',
                'contact_number' => '09120000005',
                'email' => 'kagawad2@example.com',
                'term_start' => Carbon::parse('2023-01-01'),
                'term_end' => Carbon::parse('2026-12-31'),
                'status' => 'active',
                'photo' => null,
                'remarks' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'barangay' => 'Punta',
                'full_name' => 'Elena Garcia',
                'gender' => 'Female',
                'position' => 'Kagawad',
                'committee' => 'Education',
                'address' => 'Punta, Carles',
                'contact_number' => '09120000006',
                'email' => 'kagawad3@example.com',
                'term_start' => Carbon::parse('2023-01-01'),
                'term_end' => Carbon::parse('2026-12-31'),
                'status' => 'active',
                'photo' => null,
                'remarks' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'barangay' => 'Punta',
                'full_name' => 'Mark Cruz',
                'gender' => 'Male',
                'position' => 'Kagawad',
                'committee' => 'Infrastructure',
                'address' => 'Punta, Carles',
                'contact_number' => '09120000007',
                'email' => 'kagawad4@example.com',
                'term_start' => Carbon::parse('2023-01-01'),
                'term_end' => Carbon::parse('2026-12-31'),
                'status' => 'active',
                'photo' => null,
                'remarks' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'barangay' => 'Punta',
                'full_name' => 'Rosa Bautista',
                'gender' => 'Female',
                'position' => 'Kagawad',
                'committee' => 'Environment',
                'address' => 'Punta, Carles',
                'contact_number' => '09120000008',
                'email' => 'kagawad5@example.com',
                'term_start' => Carbon::parse('2023-01-01'),
                'term_end' => Carbon::parse('2026-12-31'),
                'status' => 'active',
                'photo' => null,
                'remarks' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'barangay' => 'Punta',
                'full_name' => 'Kevin Flores',
                'gender' => 'Male',
                'position' => 'Kagawad',
                'committee' => 'Budget & Finance',
                'address' => 'Punta, Carles',
                'contact_number' => '09120000009',
                'email' => 'kagawad6@example.com',
                'term_start' => Carbon::parse('2023-01-01'),
                'term_end' => Carbon::parse('2026-12-31'),
                'status' => 'active',
                'photo' => null,
                'remarks' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'barangay' => 'Punta',
                'full_name' => 'Liza Mendez',
                'gender' => 'Female',
                'position' => 'Kagawad',
                'committee' => 'Social Services',
                'address' => 'Punta, Carles',
                'contact_number' => '09120000010',
                'email' => 'kagawad7@example.com',
                'term_start' => Carbon::parse('2023-01-01'),
                'term_end' => Carbon::parse('2026-12-31'),
                'status' => 'active',
                'photo' => null,
                'remarks' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'barangay' => 'Punta',
                'full_name' => 'Robert Tan',
                'gender' => 'Male',
                'position' => 'Kagawad',
                'committee' => 'Tourism / Sports',
                'address' => 'Punta, Carles',
                'contact_number' => '09120000011',
                'email' => 'kagawad8@example.com',
                'term_start' => Carbon::parse('2023-01-01'),
                'term_end' => Carbon::parse('2026-12-31'),
                'status' => 'active',
                'photo' => null,
                'remarks' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // =========================
            // SK CHAIRPERSON
            // =========================
            [
                'barangay' => 'Punta',
                'full_name' => 'Angela Ramos',
                'gender' => 'Female',
                'position' => 'SK Chairperson',
                'committee' => 'Youth Development',
                'address' => 'Punta, Carles',
                'contact_number' => '09120000012',
                'email' => 'sk.punta@example.com',
                'term_start' => Carbon::parse('2023-01-01'),
                'term_end' => Carbon::parse('2026-12-31'),
                'status' => 'active',
                'photo' => null,
                'remarks' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
