<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use Illuminate\Http\Request;

class ResidentController extends Controller
{
    // =========================
    // LIST (SEARCH + PAGINATION)
    // =========================
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Resident::where('barangay', $user->barangay);

        // SEARCH
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('household_number', 'like', "%{$search}%")
                    ->orWhere('barangay', 'like', "%{$search}%")
                    ->orWhere('resident_code', 'like', "%{$search}%");
            });
        }

        // FILTER: gender
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // FILTER: civil status
        if ($request->filled('civil_status')) {
            $query->where('civil_status', $request->civil_status);
        }

        // FILTER: voter
        if ($request->filled('is_voter')) {
            $query->where('is_voter', $request->is_voter);
        }

        return response()->json(
            $query->orderBy('created_at', 'desc')->paginate(10)
        );
    }

    // =========================
    // STORE (FULL VALIDATION)
    // =========================
    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            // IDENTITY
            'resident_code' => 'nullable|string|unique:residents,resident_code',
            'household_number' => 'required|string',
            'family_number' => 'nullable|string',

            // PERSONAL
            'first_name' => 'required|string',
            'middle_name' => 'nullable|string',
            'last_name' => 'required|string',
            'suffix' => 'nullable|string',
            'alias' => 'nullable|string',
            'gender' => 'required|string',
            'civil_status' => 'required|string',
            'nationality' => 'nullable|string',
            'religion' => 'nullable|string',
            'ethnicity' => 'nullable|string',

            // BIRTH
            'birth_date' => 'nullable|date',
            'age' => 'nullable|integer',
            'place_of_birth' => 'nullable|string',
            'birth_certificate_no' => 'nullable|string',

            // ADDRESS
            'region' => 'nullable|string',
            'purok_zone' => 'required|string',
            'street_address' => 'nullable|string',
            'full_address_text' => 'nullable|string',

            // HOUSEHOLD
            'household_head' => 'boolean',
            'relationship_to_head' => 'nullable|string',
            'household_role' => 'nullable|string',
            'number_of_household_members' => 'nullable|integer',
            'housing_type' => 'nullable|string',

            // CONTACT
            'mobile_number' => 'nullable|string',
            'telephone_number' => 'nullable|string',
            'email' => 'nullable|email',

            'emergency_contact_name' => 'nullable|string',
            'emergency_contact_number' => 'nullable|string',
            'emergency_contact_relationship' => 'nullable|string',

            // SOCIO ECONOMIC
            'employment_status' => 'nullable|string',
            'occupation' => 'nullable|string',
            'monthly_income' => 'nullable|numeric',
            'source_of_income' => 'nullable|string',
            'skills' => 'nullable|string',
            'educational_attainment' => 'nullable|string',
            'school_status' => 'nullable|string',

            // HEALTH
            'blood_type' => 'nullable|string',
            'disability_status' => 'boolean',
            'disability_type' => 'nullable|string',
            'medical_conditions' => 'nullable|string',
            'vaccination_status' => 'nullable|string',
            'philhealth_number' => 'nullable|string',

            // GOV IDS
            'sss_number' => 'nullable|string',
            'gsis_number' => 'nullable|string',
            'tin_number' => 'nullable|string',
            'voters_id_number' => 'nullable|string',
            'pwd_id_number' => 'nullable|string',
            'senior_citizen_id_number' => 'nullable|string',

            // RESIDENCY
            'residency_status' => 'nullable|string',
            'date_of_residency' => 'nullable|date',
            'years_of_residency' => 'nullable|integer',
            'previous_address' => 'nullable|string',

            // FLAGS
            'is_4ps_beneficiary' => 'boolean',
            'is_indigent' => 'boolean',
            'is_uct_beneficiary' => 'boolean',
            'is_voter' => 'boolean',
            'is_sk_voter' => 'boolean',
            'is_late_registration' => 'boolean',
            'status' => 'nullable|string',

            // DOCUMENTS
            'barangay_clearance_status' => 'nullable|string',
            'cedula_number' => 'nullable|string',
            'police_clearance_status' => 'nullable|string',
            'residency_certificate_status' => 'nullable|string',

            // SYSTEM
            'photo_url' => 'nullable|string',
            'signature_url' => 'nullable|string',
            'remarks' => 'nullable|string',
            'tags' => 'nullable|string',

            'created_by' => 'nullable|integer',
            'updated_by' => 'nullable|integer',
        ]);

        // AUTO GENERATE CODE IF EMPTY
        if (!$request->resident_code) {
            $validated['resident_code'] = 'RES-' . date('Y') . '-' . rand(100000, 999999);
        }

        // AUTO AGE
        if (!empty($request->birth_date)) {
            $validated['age'] = now()->diffInYears($request->birth_date);
        }
        $validated['barangay'] = $user->barangay;
        $validated['city_municipality'] = $user->municipality;
        $validated['province'] = $user->province;
        $validated['created_by'] = $user->id;
        $resident = Resident::create($validated);

        return response()->json([
            'message' => 'Resident created successfully',
            'data' => $resident
        ], 201);
    }

    // =========================
    // SHOW
    // =========================
    public function show($id)
    {
        return Resident::findOrFail($id);
    }

    // =========================
    // UPDATE (FULL SAFE UPDATE)
    // =========================
    public function update(Request $request, $id)
    {
        $resident = Resident::findOrFail($id);

        $validated = $request->validate([
            'household_number' => 'sometimes|required|string',
            'first_name' => 'sometimes|required|string',
            'last_name' => 'sometimes|required|string',
            'gender' => 'sometimes|required|string',
            'civil_status' => 'sometimes|required|string',
            'barangay' => 'sometimes|required|string',
            'purok_zone' => 'sometimes|required|string',
            'mobile_number' => 'sometimes|required|string',
        ]);

        $resident->update($validated);

        return response()->json([
            'message' => 'Updated successfully',
            'data' => $resident
        ]);
    }

    // =========================
    // DELETE
    // =========================
    public function destroy($id)
    {
        Resident::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Deleted successfully'
        ]);
    }
}
