<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Certificate;
use App\Models\User;
use Faker\Factory as Faker;

class CertificateSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        $userIds = User::pluck('id')->toArray();

        for ($i = 0; $i < 1000; $i++) {
            Certificate::create([
                'user_id' => $faker->randomElement($userIds),

                'full_name' => $faker->name, // ✅ REALISTIC NAME

                'age' => $faker->numberBetween(18, 75),
                'gender' => $faker->randomElement(['Male', 'Female']),

                'address' => $faker->address,

                'document_type' => $faker->randomElement([
                    'Barangay Clearance',
                    'Certificate of Residency',
                    'Business Permit',
                    'Certificate of Indigency',
                ]),
                'status' => $faker->randomElement(['pending', 'approved', 'rejected']),

                'purpose' => $faker->sentence(6),

                'company_name' => $faker->optional()->company,
                'business_nature' => $faker->optional()->jobTitle,
            ]);
        }
    }
}
