<?php

namespace App\Jobs;

use App\Models\DwgImport;
use App\Models\ProjectStructure;
use App\Models\ProjectFloor;
use App\Models\ProjectUnit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApplyDwgImportMappings implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300; // 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public DwgImport $dwgImport
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Applying DWG import mappings for import ID: {$this->dwgImport->id}");

        try {
            $createdRecords = $this->applyMappings();

            // Calculate counts
            $counts = [
                'structures' => count($createdRecords['structures'] ?? []),
                'floors' => count($createdRecords['floors'] ?? []),
                'units' => count($createdRecords['units'] ?? []),
            ];

            // Mark as completed
            $this->dwgImport->markAsCompleted(
                $this->dwgImport->parsed_data,
                $createdRecords,
                $counts
            );

            Log::info("DWG import mappings applied successfully for import ID: {$this->dwgImport->id}", $counts);

        } catch (\Exception $e) {
            Log::error("Failed to apply DWG import mappings for import ID: {$this->dwgImport->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->dwgImport->markAsFailed($e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            throw $e;
        }
    }

    /**
     * Apply user mappings to create database records
     */
    private function applyMappings(): array
    {
        $createdRecords = [
            'structures' => [],
            'floors' => [],
            'units' => [],
        ];

        DB::beginTransaction();

        try {
            $project = $this->dwgImport->project;
            $parsedData = $this->dwgImport->parsed_data;
            $layerMappings = $this->dwgImport->layer_mappings;
            $importType = $this->dwgImport->import_type;

            // Create or link structures based on mappings
            if ($importType === 'comprehensive' || $importType === 'structures_only') {
                $createdRecords['structures'] = $this->processStructures($project, $parsedData, $layerMappings);
            }

            // Create or link floors based on mappings
            if ($importType === 'comprehensive' || $importType === 'floors_only') {
                $createdRecords['floors'] = $this->processFloors($project, $parsedData, $layerMappings, $createdRecords['structures']);
            }

            // Create units based on mappings
            if ($importType === 'comprehensive' || $importType === 'units_only') {
                $createdRecords['units'] = $this->processUnits($project, $parsedData, $layerMappings, $createdRecords['structures'], $createdRecords['floors']);
            }

            DB::commit();

            return $createdRecords;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Process structures from parsed data and mappings
     */
    private function processStructures($project, $parsedData, $layerMappings): array
    {
        $createdStructures = [];
        $structureIndex = 1;

        foreach ($parsedData['structures'] ?? [] as $index => $structureData) {
            // Find mapping for this structure
            $mapping = $this->findMapping($layerMappings, 'structure', $index);

            if (!$mapping) {
                Log::warning("No mapping found for structure at index {$index}");
                continue;
            }

            // Skip if action is 'skip'
            if (isset($mapping['action']) && $mapping['action'] === 'skip') {
                continue;
            }

            // If user mapped to existing structure, skip creation
            if (isset($mapping['action']) && $mapping['action'] === 'link' && isset($mapping['existing_structure_id'])) {
                $structure = ProjectStructure::find($mapping['existing_structure_id']);
                if ($structure) {
                    $createdStructures[$structureData['name']] = [
                        'id' => $structure->id,
                        'name' => $structure->name,
                        'action' => 'linked_existing',
                    ];
                }
                continue;
            }

            // Create new structure
            $structureName = $mapping['target_name'] ?? $structureData['name'];
            $code = $this->generateStructureCode($structureName, $structureIndex, $project->id);

            $structure = ProjectStructure::create([
                'project_id' => $project->id,
                'code' => $code,
                'name' => $structureName,
                'description' => $structureData['description'] ?? null,
                'order' => $structureData['order'] ?? 0,
            ]);

            $createdStructures[$structureData['name']] = [
                'id' => $structure->id,
                'name' => $structure->name,
                'action' => 'created_new',
            ];

            $structureIndex++;
        }

        return $createdStructures;
    }

    /**
     * Process floors from parsed data and mappings
     */
    private function processFloors($project, $parsedData, $layerMappings, $createdStructures): array
    {
        $createdFloors = [];

        foreach ($parsedData['floors'] ?? [] as $index => $floorData) {
            // Find mapping for this floor
            $mapping = $this->findMapping($layerMappings, 'floor', $index);

            if (!$mapping) {
                Log::warning("No mapping found for floor at index {$index}");
                continue;
            }

            // Determine which structure this floor belongs to
            $structureId = null;

            if (isset($mapping['existing_structure_id'])) {
                $structureId = $mapping['existing_structure_id'];
            } elseif (isset($createdStructures[$floorData['structure_name']])) {
                $structureId = $createdStructures[$floorData['structure_name']]['id'];
            } else {
                Log::warning("Cannot determine structure for floor: {$floorData['name']}");
                continue;
            }

            // If user mapped to existing floor, skip creation
            if (isset($mapping['existing_floor_id'])) {
                $floor = ProjectFloor::find($mapping['existing_floor_id']);
                if ($floor) {
                    $createdFloors[$floorData['structure_name'] . '|' . $floorData['name']] = [
                        'id' => $floor->id,
                        'name' => $floor->name,
                        'structure_id' => $floor->structure_id,
                        'action' => 'linked_existing',
                    ];
                }
                continue;
            }

            // Create new floor
            $floor = ProjectFloor::create([
                'structure_id' => $structureId,
                'name' => $mapping['target_name'] ?? $floorData['name'],
                'floor_number' => $floorData['floor_number'] ?? 0,
                'order' => $floorData['order'] ?? 0,
            ]);

            $createdFloors[$floorData['structure_name'] . '|' . $floorData['name']] = [
                'id' => $floor->id,
                'name' => $floor->name,
                'structure_id' => $floor->structure_id,
                'action' => 'created_new',
            ];
        }

        return $createdFloors;
    }

    /**
     * Process units from parsed data and mappings
     */
    private function processUnits($project, $parsedData, $layerMappings, $createdStructures, $createdFloors): array
    {
        $createdUnits = [];

        foreach ($parsedData['units'] ?? [] as $index => $unitData) {
            // Find mapping for this unit group
            $mapping = $this->findMapping($layerMappings, 'unit', $index);

            if (!$mapping || $mapping['action'] === 'skip') {
                continue;
            }

            // Determine which floor this unit belongs to
            $floorKey = $unitData['structure_name'] . '|' . $unitData['floor_name'];
            $floorId = null;
            $structureId = null;

            if (isset($mapping['existing_floor_id'])) {
                $floorId = $mapping['existing_floor_id'];
                // Get structure_id from the floor
                $floor = ProjectFloor::find($floorId);
                $structureId = $floor ? $floor->structure_id : null;
            } elseif (isset($createdFloors[$floorKey])) {
                $floorId = $createdFloors[$floorKey]['id'];
                $structureId = $createdFloors[$floorKey]['structure_id'];
            } else {
                Log::warning("Cannot determine floor for unit: {$unitData['unit_number']}");
                continue;
            }

            if (!$structureId) {
                Log::warning("Cannot determine structure for unit: {$unitData['unit_number']}");
                continue;
            }

            // Create unit
            $unit = ProjectUnit::create([
                'structure_id' => $structureId,
                'floor_id' => $floorId,
                'unit_code' => $unitData['unit_number'],
                'gross_area' => $unitData['gross_area'] ?? null,
                'net_area' => $unitData['net_area'] ?? null,
                'status' => 'not_started',
            ]);

            $createdUnits[] = [
                'id' => $unit->id,
                'unit_code' => $unit->unit_code,
                'floor_id' => $unit->floor_id,
                'action' => 'created_new',
            ];
        }

        return $createdUnits;
    }

    /**
     * Find mapping for given type and index
     */
    private function findMapping($layerMappings, $type, $index): ?array
    {
        foreach ($layerMappings as $mapping) {
            if ($mapping['type'] === $type) {
                // Check if this mapping applies to this index
                if (isset($mapping['original_index']) && $mapping['original_index'] === $index) {
                    return $mapping;
                }
                if (isset($mapping['original_indices']) && in_array($index, $mapping['original_indices'])) {
                    return $mapping;
                }
            }
        }

        return null;
    }

    /**
     * Generate unique structure code
     */
    private function generateStructureCode(string $name, int $index, int $projectId): string
    {
        // Try to extract first letter or word from name
        $words = explode(' ', trim($name));
        $firstWord = strtoupper($words[0]);

        // Try single letter codes: A, B, C, etc.
        if (strlen($firstWord) > 0) {
            $code = substr($firstWord, 0, 1);

            // Check if code exists
            $exists = ProjectStructure::where('project_id', $projectId)
                ->where('code', $code)
                ->exists();

            if (!$exists) {
                return $code;
            }
        }

        // Fallback: Use numeric codes: BLK1, BLK2, BLK3, etc.
        $code = 'BLK' . $index;
        $counter = $index;

        while (ProjectStructure::where('project_id', $projectId)->where('code', $code)->exists()) {
            $counter++;
            $code = 'BLK' . $counter;
        }

        return $code;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("ApplyDwgImportMappings job failed permanently for import ID: {$this->dwgImport->id}", [
            'error' => $exception->getMessage(),
        ]);

        if ($this->dwgImport->status !== 'failed') {
            $this->dwgImport->markAsFailed(
                "Mapping uygulama hatası: " . $exception->getMessage(),
                [
                    'exception' => get_class($exception),
                    'attempts' => $this->attempts(),
                ]
            );
        }
    }
}
