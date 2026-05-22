<?php

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

        if (empty($userIds)) {
            throw new \Exception("No users found. Seed users first.");
        }

        for ($i = 0; $i < 1000; $i++) {
            Certificate::create([
                'user_id' => $faker->randomElement($userIds),
                'full_name' => $faker->name,
                'age' => $faker->numberBetween(18, 70),
                'gender' => $faker->randomElement(['Male', 'Female']),
                'address' => $faker->address,
                'document_type' => $faker->randomElement([
                    'Barangay Clearance',
                    'Certificate of Residency',
                    'Business Permit'
                ]),
                'purpose' => $faker->sentence,
                'company_name' => $faker->optional()->company,
                'business_nature' => $faker->optional()->jobTitle,
            ]);
        }
    }
}
