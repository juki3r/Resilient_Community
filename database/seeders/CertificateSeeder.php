<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class CertificatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 0; $i < 20; $i++) {
            DB::table('certificates')->insert([
                'user_id' => 16,
                'barangay' => 'Punta',
                'full_name' => $faker->name,
                'age' => $faker->numberBetween(18, 70),
                'gender' => $faker->randomElement(['Male', 'Female']),
                'address' => $faker->address,

                'document_type' => $faker->randomElement([
                    'Barangay Clearance',
                    'Certificate of Residency',
                    'Business Permit',
                    'Indigency Certificate'
                ]),

                'purpose' => $faker->sentence(8),

                'company_name' => $faker->optional()->company,
                'business_nature' => $faker->optional()->jobTitle,

                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
