<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use App\Models\Blotter;
use App\Models\Concern;
use App\Models\Certificate;
use App\Models\MobileUser;
use App\Models\Ordinance;
use App\Models\Incident;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $barangay = $user->barangay;

        return response()->json([
            "residents" => Resident::where('barangay', $barangay)->count(),
            "voters" => Resident::where('barangay', $barangay)->where('is_voter', 1)->count(),
            "male" => Resident::where('barangay', $barangay)->where('gender', 'Male')->count(),
            "female" => Resident::where('barangay', $barangay)->where('gender', 'Female')->count(),

            "blotters" => Blotter::where('barangay', $barangay)->count(),
            "concerns" => Concern::where('barangay', $barangay)->count(),
            "certificates" => Certificate::where('barangay', $barangay)->count(),

            "app_users" => MobileUser::where('barangay', $barangay)->count(),
            "ordinances" => Ordinance::where('barangay', $barangay)->count(),
            "incidents" => Incident::where('barangay', $barangay)->count(),

            // CHART DATA
            "incident_trend" => Incident::selectRaw("DATE(created_at) as date, COUNT(*) as total")
                ->where('barangay', $barangay)
                ->groupBy('date')
                ->orderBy('date')
                ->get(),

            "gender_distribution" => [
                ["name" => "Male", "value" => Resident::where('barangay', $barangay)->where('gender', 'Male')->count()],
                ["name" => "Female", "value" => Resident::where('barangay', $barangay)->where('gender', 'Female')->count()],
            ],
        ]);
    }
}
