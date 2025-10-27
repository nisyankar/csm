<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectStructure;
use App\Models\ProjectFloor;
use Illuminate\Http\Request;

class ProjectDataController extends Controller
{
    /**
     * Get structures (blok) for a project
     */
    public function getStructures($projectId)
    {
        $structures = ProjectStructure::where('project_id', $projectId)
            ->select('id', 'name', 'code', 'structure_type')
            ->orderBy('name')
            ->get();

        return response()->json($structures);
    }

    /**
     * Get floors (kat) for a structure
     */
    public function getFloors($structureId)
    {
        $floors = ProjectFloor::where('structure_id', $structureId)
            ->select('id', 'name as floor_name', 'floor_number')
            ->orderBy('floor_number')
            ->get();

        return response()->json($floors);
    }

    /**
     * Get units (daire) for a floor
     */
    public function getUnits($floorId)
    {
        $units = \App\Models\ProjectUnit::where('floor_id', $floorId)
            ->select('id', 'unit_code', 'unit_type', 'gross_area', 'net_area', 'status')
            ->orderBy('unit_code')
            ->get();

        return response()->json($units);
    }

    /**
     * Get available units for a floor (only for sale)
     */
    public function getAvailableUnits($floorId)
    {
        $units = \App\Models\ProjectUnit::where('floor_id', $floorId)
            ->where(function ($query) {
                $query->where('is_sold', false)
                      ->orWhereNull('is_sold');
            })
            ->whereNotIn('status', ['sold', 'delivered'])
            ->select('id', 'unit_code', 'unit_type', 'gross_area', 'net_area', 'status', 'is_sold')
            ->orderBy('unit_code')
            ->get();

        return response()->json($units);
    }
}
