<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ResidentSeeder extends Seeder
{
    public function run(): void
    {
        $barangays = [
            'Punta',
            'Poblacion',
            'Barosbos',
            'Granada',
            'Asluman',
            'Bancal',
            'Guinticgan',
            'Tarong',
            'Nalumsan'
        ];

        $firstNames = [
            'Juan',
            'Maria',
            'Jose',
            'Ana',
            'Pedro',
            'Luis',
            'Rosa',
            'Miguel',
            'Josefina',
            'Carlos',
            'Elena',
            'Ramon',
            'Carmen',
            'Antonio',
            'Grace'
        ];

        $lastNames = [
            'Santos',
            'Reyes',
            'Cruz',
            'Bautista',
            'Garcia',
            'Dela Cruz',
            'Torres',
            'Mendoza',
            'Navarro',
            'Aquino',
            'Ramos',
            'Lopez',
            'Flores',
            'Castillo',
            'Villanueva'
        ];

        $occupations = [
            'Tricycle Driver',
            'Vendor',
            'Teacher',
            'Construction Worker',
            'Office Clerk',
            'Housewife',
            'Security Guard',
            'Fisherman',
            'Student',
            'Call Center Agent'
        ];

        for ($i = 1; $i <= 50; $i++) {

            $first = $firstNames[array_rand($firstNames)];
            $last = $lastNames[array_rand($lastNames)];

            $birthDate = Carbon::now()->subYears(rand(18, 70))->subDays(rand(1, 3650));

            DB::table('residents')->insert([
                'resident_code' => 'R-2026-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'household_number' => 'HH-' . rand(1000, 1050),
                'family_number' => 'FAM-' . rand(500, 550),

                'first_name' => $first,
                'middle_name' => strtoupper(substr($last, 0, 1)) . '.',
                'last_name' => $last,
                'suffix' => null,
                'alias' => null,
                'gender' => rand(0, 1) ? 'Male' : 'Female',
                'civil_status' => ['Single', 'Married', 'Widowed'][rand(0, 2)],
                'nationality' => 'Filipino',
                'religion' => 'Catholic',
                'ethnicity' => 'Cebuano',

                'birth_date' => $birthDate->format('Y-m-d'),
                'age' => $birthDate->age,
                'place_of_birth' => 'Cebu City',
                'birth_certificate_no' => 'BC-' . rand(100000, 999999),

                'region' => 'Region VII',
                'province' => 'Cebu',
                'city_municipality' => 'Cebu City',
                'barangay' => $barangays[array_rand($barangays)],
                'purok_zone' => 'Purok ' . rand(1, 7),
                'street_address' => 'Sitio ' . rand(1, 20),
                'full_address_text' => 'Cebu City, Philippines',

                'household_head' => (bool)rand(0, 1),
                'relationship_to_head' => 'Member',
                'household_role' => ['head', 'member'][rand(0, 1)],
                'number_of_household_members' => rand(2, 8),
                'housing_type' => ['Owned', 'Rented', 'Informal Settler'][rand(0, 2)],

                'mobile_number' => '09' . rand(100000000, 999999999),
                'telephone_number' => null,
                'email' => strtolower($first) . $i . '@mail.com',

                'emergency_contact_name' => 'Maria ' . $last,
                'emergency_contact_number' => '09' . rand(100000000, 999999999),
                'emergency_contact_relationship' => 'Spouse',

                'employment_status' => ['Employed', 'Unemployed', 'Self-employed'][rand(0, 2)],
                'occupation' => $occupations[array_rand($occupations)],
                'monthly_income' => rand(5000, 35000),
                'source_of_income' => 'Work',
                'skills' => json_encode(['Cooking', 'Driving', 'Carpentry']),
                'educational_attainment' => ['Elementary', 'High School', 'College'][rand(0, 2)],
                'school_status' => 'Not Studying',

                'blood_type' => ['A', 'B', 'AB', 'O'][rand(0, 3)],
                'disability_status' => (bool)rand(0, 10) === 1,
                'disability_type' => null,
                'medical_conditions' => null,
                'vaccination_status' => 'Complete',

                'philhealth_number' => 'PH-' . rand(100000000, 999999999),

                'sss_number' => null,
                'gsis_number' => null,
                'tin_number' => 'TIN-' . rand(100000000, 999999999),
                'voters_id_number' => 'V-' . rand(100000, 999999),
                'pwd_id_number' => null,
                'senior_citizen_id_number' => null,

                'residency_status' => ['permanent', 'transient'][rand(0, 1)],
                'date_of_residency' => Carbon::now()->subYears(rand(1, 30))->format('Y-m-d'),
                'years_of_residency' => rand(1, 30),
                'previous_address' => 'Province of Cebu',

                'is_4ps_beneficiary' => (bool)rand(0, 1),
                'is_indigent' => (bool)rand(0, 1),
                'is_uct_beneficiary' => (bool)rand(0, 1),
                'is_voter' => (bool)rand(0, 1),
                'is_sk_voter' => (bool)rand(0, 1),
                'is_late_registration' => false,
                'status' => 'active',

                'barangay_clearance_status' => 'none',
                'cedula_number' => 'CED-' . rand(10000, 99999),
                'police_clearance_status' => 'none',
                'residency_certificate_status' => 'none',

                'photo_url' => null,
                'signature_url' => null,
                'remarks' => null,
                'tags' => json_encode(['sample', 'seed']),

                'created_by' => 1,
                'updated_by' => 1,

                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
