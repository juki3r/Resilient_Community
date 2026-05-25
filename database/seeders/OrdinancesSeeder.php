<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrdinancesSeeder extends Seeder
{
    public function run(): void
    {
        $year = date('Y');
        $count = 1;

        $makeNumber = function () use (&$year, &$count) {
            return 'ORD-' . $year . '-' . str_pad($count++, 3, '0', STR_PAD_LEFT);
        };

        $now = now();

        DB::table('ordinances')->insert([

            // ==============================
            // ENVIRONMENT & WASTE
            // ==============================
            [
                'barangay' => 'Punta',
                'ordinance_number' => $makeNumber(),
                'title' => 'Proper Waste Segregation Ordinance',
                'description' => 'All households are required to segregate biodegradable, recyclable, and residual waste.',
                'category' => 'Environment',
                'status' => 'active',
                'effectivity_date' => Carbon::parse('2025-01-01'),
                'approved_date' => Carbon::parse('2024-12-01'),
                'penalties' => '₱500 fine and community service for violations',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'barangay' => 'Punta',
                'ordinance_number' => $makeNumber(),
                'title' => 'Anti-Littering Ordinance',
                'description' => 'Littering in streets, canals, and public areas is strictly prohibited.',
                'category' => 'Environment',
                'status' => 'active',
                'effectivity_date' => Carbon::parse('2025-01-01'),
                'approved_date' => Carbon::parse('2024-12-01'),
                'penalties' => '₱300 fine per offense',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ==============================
            // SAFETY & SECURITY
            // ==============================
            [
                'barangay' => 'Punta',
                'ordinance_number' => $makeNumber(),
                'title' => 'Curfew Ordinance for Minors',
                'description' => 'Minors are prohibited from loitering outside after 10:00 PM without guardian.',
                'category' => 'Safety',
                'status' => 'active',
                'effectivity_date' => Carbon::parse('2025-01-10'),
                'approved_date' => Carbon::parse('2024-12-15'),
                'penalties' => 'Warning for first offense, sanctions for guardians',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'barangay' => 'Punta',
                'ordinance_number' => $makeNumber(),
                'title' => 'Anti-Loitering Ordinance',
                'description' => 'Loitering in dark or restricted areas is prohibited for safety reasons.',
                'category' => 'Safety',
                'status' => 'active',
                'effectivity_date' => Carbon::parse('2025-01-15'),
                'approved_date' => Carbon::parse('2024-12-20'),
                'penalties' => 'Warning or community service',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ==============================
            // PUBLIC ORDER / NOISE
            // ==============================
            [
                'barangay' => 'Punta',
                'ordinance_number' => $makeNumber(),
                'title' => 'Noise Control Ordinance',
                'description' => 'Excessive noise after 9:00 PM is prohibited in residential areas.',
                'category' => 'Public Order',
                'status' => 'active',
                'effectivity_date' => Carbon::parse('2025-02-01'),
                'approved_date' => Carbon::parse('2025-01-10'),
                'penalties' => '₱300 fine or confiscation of sound system',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'barangay' => 'Punta',
                'ordinance_number' => $makeNumber(),
                'title' => 'Videoke Time Regulation',
                'description' => 'Videoke use allowed only until 10:00 PM.',
                'category' => 'Public Order',
                'status' => 'active',
                'effectivity_date' => Carbon::parse('2025-02-05'),
                'approved_date' => Carbon::parse('2025-01-15'),
                'penalties' => 'Confiscation of equipment for repeat violations',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ==============================
            // HEALTH
            // ==============================
            [
                'barangay' => 'Punta',
                'ordinance_number' => $makeNumber(),
                'title' => 'Anti-Smoking Ordinance',
                'description' => 'Smoking is prohibited in public places including streets and parks.',
                'category' => 'Health',
                'status' => 'active',
                'effectivity_date' => Carbon::parse('2025-03-01'),
                'approved_date' => Carbon::parse('2025-02-01'),
                'penalties' => '₱500 fine per violation',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'barangay' => 'Punta',
                'ordinance_number' => $makeNumber(),
                'title' => 'Anti-Dengue Cleanup Ordinance',
                'description' => 'Mandatory weekly cleaning of surroundings to prevent dengue breeding sites.',
                'category' => 'Health',
                'status' => 'active',
                'effectivity_date' => Carbon::parse('2025-03-10'),
                'approved_date' => Carbon::parse('2025-02-15'),
                'penalties' => '₱200 fine or community cleanup duty',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ==============================
            // ANIMAL CONTROL
            // ==============================
            [
                'barangay' => 'Punta',
                'ordinance_number' => $makeNumber(),
                'title' => 'Stray Animals Control Ordinance',
                'description' => 'Pet owners must secure their animals; stray animals will be impounded.',
                'category' => 'Animal Control',
                'status' => 'active',
                'effectivity_date' => Carbon::parse('2025-03-15'),
                'approved_date' => Carbon::parse('2025-02-20'),
                'penalties' => 'Impound fee + penalties for negligence',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ==============================
            // TRAFFIC / ROAD USE
            // ==============================
            [
                'barangay' => 'Punta',
                'ordinance_number' => $makeNumber(),
                'title' => 'No Parking in Narrow Streets',
                'description' => 'Parking in designated narrow roads and intersections is prohibited.',
                'category' => 'Traffic',
                'status' => 'active',
                'effectivity_date' => Carbon::parse('2025-04-01'),
                'approved_date' => Carbon::parse('2025-03-10'),
                'penalties' => '₱300 fine or towing coordination',
                'created_at' => $now,
                'updated_at' => $now,
            ],

        ]);
    }
}
