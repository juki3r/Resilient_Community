<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CertificateSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = ['pending', 'approved', 'rejected'];

        $documents = [
            'Barangay Clearance',
            'Certificate of Residency',
            'Certificate of Indigency',
            'Business Permit',
            'Certificate of Low Income',
            'Certificate of Good Moral Character',
        ];

        $genders = ['Male', 'Female'];

        $now = Carbon::now();

        $batch = [];

        // 👇 5 users
        for ($userId = 1; $userId <= 5; $userId++) {

            // 👇 1000 records per user
            for ($i = 1; $i <= 1000; $i++) {

                $batch[] = [
                    'user_id' => $userId,
                    'full_name' => "Test User {$userId}-{$i}",
                    'age' => rand(18, 70),
                    'gender' => $genders[array_rand($genders)],
                    'address' => "Sample Address {$i}, City",
                    'document_type' => $documents[array_rand($documents)],
                    'purpose' => "Auto generated test data #{$i}",
                    'company_name' => null,
                    'business_nature' => null,
                    'status' => $statuses[array_rand($statuses)],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                // 🔥 insert in chunks (prevents memory crash)
                if (count($batch) === 500) {
                    DB::table('certificates')->insert($batch);
                    $batch = [];
                }
            }
        }

        // final insert
        if (!empty($batch)) {
            DB::table('certificates')->insert($batch);
        }
    }
}
