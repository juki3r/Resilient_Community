<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Blotter;
use Carbon\Carbon;

class BlotterSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['Theft', 'Assault', 'Dispute', 'Noise Complaint', 'Vandalism'];
        $categories = ['Minor', 'Major', 'Emergency', 'Peace & Order'];
        $locations = ['Purok 1', 'Purok 2', 'Purok 3', 'Main Road', 'Sitio Centro'];
        $barangays = ['Poblacion', 'Bancal', 'Barosbos', 'Punta', 'Nalumsan'];
        $municipalities = ['Carles', 'Estancia', 'Balasan'];
        $provinces = ['Cebu'];

        for ($i = 1; $i <= 20; $i++) {

            $year = date('Y');

            Blotter::create([
                'blotter_number' => 'BLT-' . $year . '-' . str_pad($i, 6, '0', STR_PAD_LEFT),

                'incident_type' => $types[array_rand($types)],
                'incident_category' => $categories[array_rand($categories)],

                'incident_date' => Carbon::now()->subDays(rand(1, 365))->format('Y-m-d'),
                'incident_time' => Carbon::now()->subMinutes(rand(1, 1440))->format('H:i:s'),

                'incident_location' => $locations[array_rand($locations)],
                'incident_details' => 'Sample incident report generated for testing purposes.',

                'complainant_id' => null,
                'complainant_name' => 'Juan Dela Cruz ' . $i,
                'complainant_contact' => '09' . rand(100000000, 999999999),
                'complainant_address' => 'Sample Address ' . $i,

                'respondent_id' => null,
                'respondent_name' => 'John Doe ' . $i,
                'respondent_contact' => '09' . rand(100000000, 999999999),
                'respondent_address' => 'Sample Address ' . $i,

                'witness_name' => 'Witness ' . $i,
                'witness_contact' => '09' . rand(100000000, 999999999),
                'witness_address' => 'Witness Address ' . $i,

                'reported_by' => 'Officer ' . rand(1, 5),
                'handled_by' => 'Barangay Captain',

                'assigned_officer' => 'Officer ' . rand(1, 5),

                'action_taken' => 'Initial investigation conducted.',
                'resolution' => rand(0, 1) ? 'Settled amicably' : null,
                'settlement_date' => rand(0, 1) ? Carbon::now()->subDays(rand(1, 30)) : null,

                'status' => ['Pending', 'Ongoing', 'Resolved'][array_rand(['Pending', 'Ongoing', 'Resolved'])],
                'priority_level' => ['Low', 'Medium', 'High'][array_rand(['Low', 'Medium', 'High'])],

                'attachment' => null,
                'evidence_photo' => null,

                'barangay' => $barangays[array_rand($barangays)],
                'municipality' => $municipalities[array_rand($municipalities)],
                'province' => $provinces[array_rand($provinces)],

                'remarks' => 'Seeded blotter record for testing.',
            ]);
        }
    }
}
