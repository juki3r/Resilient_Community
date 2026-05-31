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
    // public function index(Request $request)
    // {
    //     $user = auth()->user();

    //     $role = $user->role;
    //     $barangay = $user->barangay;
    //     $municipality = $user->municipality;

    //     // ================= BASE QUERY SCOPE =================
    //     $residentQuery = Resident::query();
    //     $incidentQuery = Incident::query();
    //     $blotterQuery = Blotter::query();
    //     $concernQuery = Concern::query();
    //     $certificateQuery = Certificate::query();
    //     $appUserQuery = MobileUser::query();
    //     $ordinanceQuery = Ordinance::query();

    //     if ($role === "bdrrmo_admin") {
    //         $residentQuery->where('barangay', $barangay);
    //         $incidentQuery->where('barangay', $barangay);
    //         $blotterQuery->where('barangay', $barangay);
    //         $concernQuery->where('barangay', $barangay);
    //         $certificateQuery->where('barangay', $barangay);
    //         $appUserQuery->where('barangay', $barangay);
    //         $ordinanceQuery->where('barangay', $barangay);
    //     }

    //     if ($role === "mdrrmo_admin") {
    //         $residentQuery->where('municipality', $municipality);
    //         $incidentQuery->where('municipality', $municipality);
    //         $blotterQuery->where('municipality', $municipality);
    //         $concernQuery->where('municipality', $municipality);
    //         $certificateQuery->where('municipality', $municipality);
    //         $appUserQuery->where('municipality', $municipality);
    //         $ordinanceQuery->where('municipality', $municipality);
    //     }

    //     return response()->json([
    //         "role" => $role,

    //         // ================= STATS =================
    //         "residents" => $residentQuery->count(),
    //         "voters" => (clone $residentQuery)->where('is_voter', 1)->count(),
    //         "male" => (clone $residentQuery)->where('gender', 'Male')->count(),
    //         "female" => (clone $residentQuery)->where('gender', 'Female')->count(),

    //         "blotters" => $blotterQuery->count(),
    //         "concerns" => $concernQuery->count(),
    //         "certificates" => $certificateQuery->count(),

    //         "app_users" => $appUserQuery->count(),
    //         "ordinances" => $ordinanceQuery->count(),
    //         "incidents" => $incidentQuery->count(),

    //         // ================= CHARTS =================
    //         "incident_trend" => $incidentQuery
    //             ->selectRaw("DATE(created_at) as date, COUNT(*) as total")
    //             ->groupBy('date')
    //             ->orderBy('date')
    //             ->get(),

    //         "gender_distribution" => [
    //             [
    //                 "name" => "Male",
    //                 "value" => (clone $residentQuery)->where('gender', 'Male')->count()
    //             ],
    //             [
    //                 "name" => "Female",
    //                 "value" => (clone $residentQuery)->where('gender', 'Female')->count()
    //             ],
    //         ],
    //         "age_distribution" => [
    //             [
    //                 "range" => "0-17",
    //                 "count" => Resident::where('barangay', $barangay)
    //                     ->whereBetween('age', [0, 17])
    //                     ->count()
    //             ],
    //             [
    //                 "range" => "18-30",
    //                 "count" => Resident::where('barangay', $barangay)
    //                     ->whereBetween('age', [18, 30])
    //                     ->count()
    //             ],
    //             [
    //                 "range" => "31-59",
    //                 "count" => Resident::where('barangay', $barangay)
    //                     ->whereBetween('age', [31, 59])
    //                     ->count()
    //             ],
    //             [
    //                 "range" => "60+",
    //                 "count" => Resident::where('barangay', $barangay)
    //                     ->where('age', '>=', 60)
    //                     ->count()
    //             ],
    //         ],
    //         "live_incidents" => Incident::query()
    //             ->when($role === "bdrrmo_admin", function ($q) use ($barangay) {
    //                 $q->where('barangay', $barangay);
    //             })
    //             ->when($role === "mdrrmo_admin", function ($q) use ($municipality) {
    //                 $q->where('municipality', $municipality);
    //             })
    //             ->orderByDesc('created_at')
    //             ->limit(10)
    //             ->get([
    //                 'id',
    //                 'type',
    //                 'location',
    //                 'status',
    //                 'incident_datetime',
    //                 'created_at'
    //             ]),


    //     ]);
    // }

    public function index(Request $request)
    {
        $user = auth()->user();

        abort_unless(
            $user && $user->role === 'bdrrmo_admin' && $user->barangay,
            403,
            'Unauthorized'
        );

        $barangay = $user->barangay;

        // ================= SCOPED QUERIES =================
        $residentQuery = Resident::where('barangay', $barangay);
        $incidentQuery = Incident::where('barangay', $barangay);
        $blotterQuery = Blotter::where('barangay', $barangay);
        $concernQuery = Concern::where('barangay', $barangay);
        $certificateQuery = Certificate::where('barangay', $barangay);
        $appUserQuery = MobileUser::where('barangay', $barangay);
        $ordinanceQuery = Ordinance::where('barangay', $barangay);

        return response()->json([
            "role" => $user->role,

            // ================= STATS =================
            "residents" => $residentQuery->count(),
            "voters" => (clone $residentQuery)->where('is_voter', 1)->count(),
            "male" => (clone $residentQuery)->where('gender', 'Male')->count(),
            "female" => (clone $residentQuery)->where('gender', 'Female')->count(),

            "blotters" => $blotterQuery->count(),
            "concerns" => $concernQuery->count(),
            "certificates" => $certificateQuery->count(),

            "app_users" => $appUserQuery->count(),
            "ordinances" => $ordinanceQuery->count(),
            "incidents" => $incidentQuery->count(),

            // ================= CHARTS =================
            "incident_trend" => $incidentQuery
                ->selectRaw("DATE(created_at) as date, COUNT(*) as total")
                ->groupBy('date')
                ->orderBy('date')
                ->get(),

            "gender_distribution" => [
                [
                    "name" => "Male",
                    "value" => (clone $residentQuery)->where('gender', 'Male')->count()
                ],
                [
                    "name" => "Female",
                    "value" => (clone $residentQuery)->where('gender', 'Female')->count()
                ],
            ],

            "age_distribution" => [
                [
                    "range" => "0-17",
                    "count" => (clone $residentQuery)->whereBetween('age', [0, 17])->count()
                ],
                [
                    "range" => "18-30",
                    "count" => (clone $residentQuery)->whereBetween('age', [18, 30])->count()
                ],
                [
                    "range" => "31-59",
                    "count" => (clone $residentQuery)->whereBetween('age', [31, 59])->count()
                ],
                [
                    "range" => "60+",
                    "count" => (clone $residentQuery)->where('age', '>=', 60)->count()
                ],
            ],

            // "live_incidents" => $incidentQuery
            //     ->orderByDesc('created_at')
            //     ->limit(10)
            //     ->get([
            //         'id',
            //         'type',
            //         'location',
            //         'status',
            //         'incident_datetime',
            //         'created_at'
            //     ]),
            "live_incidents" => Incident::query()
                ->when($user->role === "bdrrmo_admin", function ($q) use ($barangay) {
                    $q->where('barangay', $barangay);
                })
                ->orderByDesc('created_at')
                ->limit(10)
                ->get([
                    'id',
                    'type',
                    'location',
                    'status',
                    'incident_datetime',
                    'created_at'
                ]),

        ]);
    }
}
