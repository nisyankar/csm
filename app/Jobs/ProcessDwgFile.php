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
use Illuminate\Support\Facades\Storage;

class ProcessDwgFile implements ShouldQueue
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
    public $timeout = 600; // 10 minutes

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
        Log::info("DWG Processing started for import ID: {$this->dwgImport->id}");

        try {
            // Mark as processing
            $this->dwgImport->markAsProcessing();

            // Get the full file path
            $filePath = Storage::disk('local')->path($this->dwgImport->file_path);

            if (!file_exists($filePath)) {
                throw new \Exception("DWG dosyası bulunamadı: {$filePath}");
            }

            // Execute Python script
            $parsedData = $this->executePythonParser($filePath);

            if (!$parsedData['success']) {
                throw new \Exception($parsedData['message'] ?? 'DWG parse hatası');
            }

            // Extract layer information for user review
            $detectedLayers = $this->extractLayerInformation($parsedData['data']);

            // Mark as ready for review (user will map layers and approve)
            $this->dwgImport->markAsReadyForReview(
                $parsedData['data'],
                $detectedLayers
            );

            Log::info("DWG Processing completed successfully for import ID: {$this->dwgImport->id}. Ready for user review.", [
                'detected_layers_count' => count($detectedLayers),
                'structures_count' => count($parsedData['data']['structures'] ?? []),
                'floors_count' => count($parsedData['data']['floors'] ?? []),
                'units_count' => count($parsedData['data']['units'] ?? []),
            ]);

        } catch (\Exception $e) {
            Log::error("DWG Processing failed for import ID: {$this->dwgImport->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->dwgImport->markAsFailed($e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // Re-throw to mark job as failed
            throw $e;
        }
    }

    /**
     * Execute Python parser script
     */
    private function executePythonParser(string $filePath): array
    {
        $scriptPath = base_path('scripts/parse_dwg.py');

        if (!file_exists($scriptPath)) {
            throw new \Exception("Python parser script bulunamadı: {$scriptPath}");
        }

        // Determine Python executable
        $pythonExecutable = $this->getPythonExecutable();

        // Build command
        $command = sprintf(
            '%s %s %s 2>&1',
            escapeshellarg($pythonExecutable),
            escapeshellarg($scriptPath),
            escapeshellarg($filePath)
        );

        Log::info("Executing Python command: {$command}");

        // Execute command
        exec($command, $output, $returnCode);

        // Join output lines
        $jsonOutput = implode("\n", $output);

        Log::info("Python script output:", [
            'return_code' => $returnCode,
            'output' => $jsonOutput,
        ]);

        // Parse JSON output
        $result = json_decode($jsonOutput, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Python çıktısı JSON parse edilemedi: " . json_last_error_msg() . "\nOutput: " . $jsonOutput);
        }

        if (!is_array($result)) {
            throw new \Exception("Python çıktısı geçersiz format");
        }

        return $result;
    }

    /**
     * Extract layer information from parsed data for user review
     */
    private function extractLayerInformation(array $data): array
    {
        $layers = [];

        // Extract unique layer information from structures
        foreach ($data['structures'] ?? [] as $index => $structure) {
            $layers[] = [
                'type' => 'structure',
                'source_name' => $structure['name'],
                'description' => $structure['description'] ?? '',
                'suggested_mapping' => null, // Kullanıcı eşleştirecek
                'auto_detected' => true,
                'item_count' => 1,
                'original_index' => $index,
            ];
        }

        // Extract unique layer information from floors
        $floorGroups = [];
        foreach ($data['floors'] ?? [] as $index => $floor) {
            $key = $floor['structure_name'] . '|' . $floor['name'];
            if (!isset($floorGroups[$key])) {
                $floorGroups[$key] = [
                    'type' => 'floor',
                    'source_name' => $floor['name'],
                    'structure_name' => $floor['structure_name'],
                    'floor_number' => $floor['floor_number'] ?? 0,
                    'description' => "Yapı: {$floor['structure_name']}, Kat No: {$floor['floor_number']}",
                    'suggested_mapping' => null,
                    'auto_detected' => true,
                    'item_count' => 1,
                    'original_indices' => [$index],
                ];
            } else {
                $floorGroups[$key]['item_count']++;
                $floorGroups[$key]['original_indices'][] = $index;
            }
        }
        $layers = array_merge($layers, array_values($floorGroups));

        // Extract unique layer information from units
        $unitGroups = [];
        foreach ($data['units'] ?? [] as $index => $unit) {
            $key = $unit['structure_name'] . '|' . $unit['floor_name'];
            if (!isset($unitGroups[$key])) {
                $unitGroups[$key] = [
                    'type' => 'unit',
                    'source_name' => "Birimler ({$unit['floor_name']})",
                    'structure_name' => $unit['structure_name'],
                    'floor_name' => $unit['floor_name'],
                    'description' => "Yapı: {$unit['structure_name']}, Kat: {$unit['floor_name']}",
                    'suggested_mapping' => null,
                    'auto_detected' => true,
                    'item_count' => 1,
                    'sample_unit' => $unit['unit_number'] ?? '',
                    'gross_area' => $unit['gross_area'] ?? null,
                    'net_area' => $unit['net_area'] ?? null,
                    'total_gross_area' => $unit['gross_area'] ?? 0,
                    'total_net_area' => $unit['net_area'] ?? 0,
                    'original_indices' => [$index],
                ];
            } else {
                $unitGroups[$key]['item_count']++;
                $unitGroups[$key]['original_indices'][] = $index;
                // Accumulate total areas
                $unitGroups[$key]['total_gross_area'] += $unit['gross_area'] ?? 0;
                $unitGroups[$key]['total_net_area'] += $unit['net_area'] ?? 0;
                // Keep first unit's individual area as sample
                if (!isset($unitGroups[$key]['gross_area'])) {
                    $unitGroups[$key]['gross_area'] = $unit['gross_area'] ?? null;
                    $unitGroups[$key]['net_area'] = $unit['net_area'] ?? null;
                }
            }
        }
        $layers = array_merge($layers, array_values($unitGroups));

        return $layers;
    }

    /**
     * Get Python executable path
     */
    private function getPythonExecutable(): string
    {
        // Try different Python executables
        $possiblePaths = [
            'python3',
            'python',
            '/usr/bin/python3',
            '/usr/bin/python',
            '/usr/local/bin/python3',
            '/usr/local/bin/python',
            'C:\\Python311\\python.exe',
            'C:\\Python310\\python.exe',
            'C:\\Python39\\python.exe',
        ];

        foreach ($possiblePaths as $path) {
            exec(sprintf('%s --version 2>&1', escapeshellarg($path)), $output, $returnCode);
            if ($returnCode === 0) {
                Log::info("Found Python executable: {$path}");
                return $path;
            }
        }

        // Default to 'python3'
        Log::warning("Could not verify Python installation, using default 'python3'");
        return 'python3';
    }

    /**
     * Create project records from parsed data
     */
    private function createProjectRecords(array $data): array
    {
        $createdStructures = [
            'structures' => [],
            'floors' => [],
            'units' => [],
        ];

        DB::beginTransaction();

        try {
            $project = $this->dwgImport->project;

            // Create structures
            $structureIndex = 1;
            foreach ($data['structures'] ?? [] as $structureData) {
                // Generate code from structure name or use index
                $code = $this->generateStructureCode($structureData['name'], $structureIndex, $project->id);

                $structure = ProjectStructure::create([
                    'project_id' => $project->id,
                    'code' => $code,
                    'name' => $structureData['name'],
                    'description' => $structureData['description'] ?? null,
                    'order' => $structureData['order'] ?? 0,
                ]);

                $createdStructures['structures'][] = [
                    'id' => $structure->id,
                    'name' => $structure->name,
                ];

                $structureIndex++;
            }

            // Create floors
            foreach ($data['floors'] ?? [] as $floorData) {
                // Find structure by name
                $structure = ProjectStructure::where('project_id', $project->id)
                    ->where('name', $floorData['structure_name'])
                    ->first();

                if (!$structure) {
                    Log::warning("Structure not found for floor: {$floorData['name']}, structure: {$floorData['structure_name']}");
                    continue;
                }

                $floor = ProjectFloor::create([
                    'structure_id' => $structure->id,
                    'name' => $floorData['name'],
                    'floor_number' => $floorData['floor_number'] ?? 0,
                    'order' => $floorData['order'] ?? 0,
                ]);

                $createdStructures['floors'][] = [
                    'id' => $floor->id,
                    'name' => $floor->name,
                    'structure_name' => $structure->name,
                ];
            }

            // Create units
            foreach ($data['units'] ?? [] as $unitData) {
                // Find structure by name
                $structure = ProjectStructure::where('project_id', $project->id)
                    ->where('name', $unitData['structure_name'])
                    ->first();

                if (!$structure) {
                    Log::warning("Structure not found for unit: {$unitData['unit_number']}");
                    continue;
                }

                // Find floor by name and structure
                $floor = ProjectFloor::where('structure_id', $structure->id)
                    ->where('name', $unitData['floor_name'])
                    ->first();

                if (!$floor) {
                    Log::warning("Floor not found for unit: {$unitData['unit_number']}, floor: {$unitData['floor_name']}");
                    continue;
                }

                $unit = ProjectUnit::create([
                    'floor_id' => $floor->id,
                    'unit_number' => $unitData['unit_number'],
                    'gross_area' => $unitData['gross_area'] ?? null,
                    'net_area' => $unitData['net_area'] ?? null,
                    'status' => 'available', // Default status
                ]);

                $createdStructures['units'][] = [
                    'id' => $unit->id,
                    'unit_number' => $unit->unit_number,
                    'floor_name' => $floor->name,
                    'structure_name' => $structure->name,
                ];
            }

            DB::commit();

            return $createdStructures;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
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
        Log::error("DWG Processing job failed permanently for import ID: {$this->dwgImport->id}", [
            'error' => $exception->getMessage(),
        ]);

        // Mark as failed if not already marked
        if ($this->dwgImport->status !== 'failed') {
            $this->dwgImport->markAsFailed(
                "İş kuyruğu hatası: " . $exception->getMessage(),
                [
                    'exception' => get_class($exception),
                    'attempts' => $this->attempts(),
                ]
            );
        }
    }
}
