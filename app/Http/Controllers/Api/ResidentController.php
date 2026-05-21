<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use Illuminate\Http\Request;

class ResidentController extends Controller
{
    // PAGINATED LIST (SERVER SIDE)
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search');

        $query = Resident::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                    ->orWhere('last_name', 'like', "%$search%");
            });
        }

        return response()->json(
            $query->latest()->paginate($perPage)
        );
    }

    // SHOW SINGLE RESIDENT (PROFILE PAGE)
    public function show($id)
    {
        return Resident::findOrFail($id);
    }

    public function store(Request $request)
    {
        return Resident::create($request->all());
    }

    public function update(Request $request, $id)
    {
        $resident = Resident::findOrFail($id);
        $resident->update($request->all());

        return $resident;
    }

    public function destroy($id)
    {
        Resident::destroy($id);
        return response()->json(['message' => 'Deleted']);
    }
}
