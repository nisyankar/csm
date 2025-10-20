<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\Employee;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        $documentTypes = [
            'contract' => ['Contract', ['pdf', 'doc', 'docx']],
            'report' => ['Report', ['pdf', 'doc', 'docx', 'xlsx']],
            'invoice' => ['Invoice', ['pdf', 'xlsx']],
            'certificate' => ['Certificate', ['pdf', 'jpg', 'png']],
            'policy' => ['Policy Document', ['pdf', 'doc', 'docx']],
            'form' => ['Form', ['pdf', 'doc', 'docx']],
            'presentation' => ['Presentation', ['ppt', 'pptx', 'pdf']],
            'image' => ['Image', ['jpg', 'jpeg', 'png', 'gif']],
            'spreadsheet' => ['Spreadsheet', ['xlsx', 'xls', 'csv']],
            'text' => ['Text Document', ['txt', 'doc', 'docx']],
            'archive' => ['Archive', ['zip', 'rar', '7z']],
            'video' => ['Video', ['mp4', 'avi', 'mov']],
            'audio' => ['Audio', ['mp3', 'wav', 'aac']],
            'design' => ['Design File', ['psd', 'ai', 'sketch', 'fig']],
            'code' => ['Code File', ['js', 'php', 'py', 'java', 'cpp']],
        ];

        $category = $this->faker->randomKey($documentTypes);
        $categoryInfo = $documentTypes[$category];
        $extension = $this->faker->randomElement($categoryInfo[1]);
        
        $fileName = $this->generateFileName($category, $extension);
        $fileSize = $this->generateFileSize($extension);
        
        return [
            'name' => $fileName,
            'description' => $this->generateDescription($category),
            'file_path' => 'documents/' . date('Y/m/') . $fileName,
            'file_name' => $fileName,
            'file_size' => $fileSize,
            'mime_type' => $this->getMimeType($extension),
            'file_extension' => $extension,
            'category' => $category,
            'tags' => $this->faker->optional(0.6)->words(3, true),
            'uploaded_by' => Employee::factory(),
            'project_id' => $this->faker->optional(0.7)->randomElement([1, 2, 3, 4, 5]),
            'employee_id' => $this->faker->optional(0.5)->randomElement([1, 2, 3, 4, 5]),
            'version' => $this->faker->randomFloat(1, 1.0, 5.9),
            'is_public' => $this->faker->boolean(30), // 30% public
            'is_confidential' => $this->faker->boolean(20), // 20% confidential
            'access_level' => $this->faker->randomElement(['public', 'internal', 'restricted', 'confidential']),
            'download_count' => $this->faker->numberBetween(0, 100),
            'last_downloaded_at' => $this->faker->optional(0.7)->dateTimeBetween('-6 months', 'now'),
            'last_downloaded_by' => $this->faker->optional(0.7)->randomElement([1, 2, 3, 4, 5]),
            'expires_at' => $this->faker->optional(0.3)->dateTimeBetween('+1 month', '+2 years'),
            'password_protected' => $this->faker->boolean(15), // 15% password protected
            'checksum' => $this->faker->sha256(),
            'metadata' => json_encode([
                'upload_ip' => $this->faker->ipv4(),
                'upload_device' => $this->faker->randomElement(['web', 'mobile', 'api']),
                'scan_status' => $this->faker->randomElement(['clean', 'pending', 'quarantine']),
                'ocr_processed' => $this->faker->boolean(40),
                'thumbnail_generated' => in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'pdf']),
            ]),
            'notes' => $this->faker->optional(0.3)->paragraph(),
        ];
    }

    /**
     * Generate realistic file name
     */
    private function generateFileName(string $category, string $extension): string
    {
        $patterns = [
            'contract' => ['Contract_', 'Agreement_', 'Service_Contract_'],
            'report' => ['Monthly_Report_', 'Project_Report_', 'Analysis_', 'Summary_'],
            'invoice' => ['Invoice_', 'Bill_', 'Receipt_'],
            'certificate' => ['Certificate_', 'Diploma_', 'Award_'],
            'policy' => ['Policy_', 'Guidelines_', 'Procedures_'],
            'form' => ['Application_Form_', 'Request_Form_', 'Survey_'],
            'presentation' => ['Presentation_', 'Slides_', 'Deck_'],
            'image' => ['Photo_', 'Image_', 'Screenshot_'],
            'spreadsheet' => ['Data_', 'Report_', 'Analysis_'],
            'text' => ['Document_', 'Notes_', 'Memo_'],
            'archive' => ['Archive_', 'Backup_', 'Files_'],
            'video' => ['Video_', 'Recording_', 'Training_'],
            'audio' => ['Audio_', 'Recording_', 'Meeting_'],
            'design' => ['Design_', 'Mockup_', 'Template_'],
            'code' => ['Source_', 'Script_', 'Code_'],
        ];

        $prefix = $this->faker->randomElement($patterns[$category] ?? ['Document_']);
        $suffix = $this->faker->randomElement([
            date('Y-m-d'),
            $this->faker->numerify('###'),
            $this->faker->lexify('???'),
            'v' . $this->faker->randomFloat(1, 1, 5),
        ]);

        return $prefix . $suffix . '.' . $extension;
    }

    /**
     * Generate realistic file size based on extension
     */
    private function generateFileSize(string $extension): int
    {
        $sizeRanges = [
            'txt' => [1024, 50 * 1024], // 1KB - 50KB
            'doc' => [10 * 1024, 5 * 1024 * 1024], // 10KB - 5MB
            'docx' => [10 * 1024, 5 * 1024 * 1024], // 10KB - 5MB
            'pdf' => [100 * 1024, 20 * 1024 * 1024], // 100KB - 20MB
            'xlsx' => [50 * 1024, 10 * 1024 * 1024], // 50KB - 10MB
            'xls' => [50 * 1024, 10 * 1024 * 1024], // 50KB - 10MB
            'csv' => [1024, 1024 * 1024], // 1KB - 1MB
            'jpg' => [100 * 1024, 5 * 1024 * 1024], // 100KB - 5MB
            'jpeg' => [100 * 1024, 5 * 1024 * 1024], // 100KB - 5MB
            'png' => [50 * 1024, 10 * 1024 * 1024], // 50KB - 10MB
            'gif' => [50 * 1024, 2 * 1024 * 1024], // 50KB - 2MB
            'mp4' => [10 * 1024 * 1024, 500 * 1024 * 1024], // 10MB - 500MB
            'mp3' => [1024 * 1024, 20 * 1024 * 1024], // 1MB - 20MB
            'zip' => [1024 * 1024, 100 * 1024 * 1024], // 1MB - 100MB
            'ppt' => [1024 * 1024, 50 * 1024 * 1024], // 1MB - 50MB
            'pptx' => [1024 * 1024, 50 * 1024 * 1024], // 1MB - 50MB
        ];

        $range = $sizeRanges[$extension] ?? [1024, 1024 * 1024]; // Default 1KB - 1MB
        return $this->faker->numberBetween($range[0], $range[1]);
    }

    /**
     * Get MIME type for extension
     */
    private function getMimeType(string $extension): string
    {
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls' => 'application/vnd.ms-excel',
            'csv' => 'text/csv',
            'txt' => 'text/plain',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'mp4' => 'video/mp4',
            'mp3' => 'audio/mpeg',
            'zip' => 'application/zip',
            'rar' => 'application/vnd.rar',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'js' => 'application/javascript',
            'php' => 'application/x-php',
            'py' => 'text/x-python',
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    /**
     * Generate description based on category
     */
    private function generateDescription(string $category): string
    {
        $descriptions = [
            'contract' => 'Legal contract document with terms and conditions',
            'report' => 'Detailed analytical report containing findings and recommendations',
            'invoice' => 'Financial invoice document for billing purposes',
            'certificate' => 'Official certificate document for verification',
            'policy' => 'Company policy document outlining procedures and guidelines',
            'form' => 'Standard form document for data collection',
            'presentation' => 'Presentation slides for meetings and training',
            'image' => 'Image file for documentation or reference',
            'spreadsheet' => 'Data spreadsheet for analysis and calculations',
            'text' => 'Text document containing notes and information',
            'archive' => 'Compressed archive file containing multiple documents',
            'video' => 'Video file for training or documentation purposes',
            'audio' => 'Audio recording file for meetings or training',
            'design' => 'Design file for creative and development projects',
            'code' => 'Source code file for software development',
        ];

        return $descriptions[$category] ?? 'Document file for business purposes';
    }

    /**
     * Public document state
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
            'is_confidential' => false,
            'access_level' => 'public',
            'password_protected' => false,
        ]);
    }

    /**
     * Confidential document state
     */
    public function confidential(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
            'is_confidential' => true,
            'access_level' => 'confidential',
            'password_protected' => $this->faker->boolean(70),
            'download_count' => $this->faker->numberBetween(0, 20), // Less downloads for confidential
        ]);
    }

    /**
     * Contract document state
     */
    public function contract(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'category' => 'contract',
                'file_name' => 'Contract_' . $this->faker->numerify('####') . '.pdf',
                'mime_type' => 'application/pdf',
                'file_extension' => 'pdf',
                'is_confidential' => true,
                'access_level' => 'restricted',
                'expires_at' => $this->faker->dateTimeBetween('+1 year', '+3 years'),
                'tags' => 'contract, legal, agreement',
            ];
        });
    }

    /**
     * Report document state
     */
    public function report(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'category' => 'report',
                'file_name' => 'Report_' . date('Y-m') . '_' . $this->faker->numerify('###') . '.pdf',
                'mime_type' => 'application/pdf',
                'file_extension' => 'pdf',
                'access_level' => 'internal',
                'tags' => 'report, analysis, monthly',
                'project_id' => $this->faker->numberBetween(1, 5),
            ];
        });
    }

    /**
     * Image document state
     */
    public function image(): static
    {
        return $this->state(function (array $attributes) {
            $extension = $this->faker->randomElement(['jpg', 'png', 'jpeg']);
            return [
                'category' => 'image',
                'file_name' => 'Image_' . $this->faker->numerify('####') . '.' . $extension,
                'mime_type' => $extension === 'png' ? 'image/png' : 'image/jpeg',
                'file_extension' => $extension,
                'file_size' => $this->faker->numberBetween(500 * 1024, 5 * 1024 * 1024),
                'metadata' => json_encode([
                    'upload_ip' => $this->faker->ipv4(),
                    'upload_device' => 'web',
                    'thumbnail_generated' => true,
                    'image_width' => $this->faker->numberBetween(800, 4000),
                    'image_height' => $this->faker->numberBetween(600, 3000),
                ]),
            ];
        });
    }

    /**
     * Spreadsheet document state
     */
    public function spreadsheet(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'category' => 'spreadsheet',
                'file_name' => 'Data_' . date('Y-m') . '_' . $this->faker->numerify('###') . '.xlsx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'file_extension' => 'xlsx',
                'tags' => 'data, analysis, spreadsheet',
                'access_level' => 'internal',
            ];
        });
    }

    /**
     * Policy document state
     */
    public function policy(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'category' => 'policy',
                'file_name' => 'Policy_' . $this->faker->words(2, true) . '_v' . $this->faker->randomFloat(1, 1, 3) . '.pdf',
                'mime_type' => 'application/pdf',
                'file_extension' => 'pdf',
                'is_public' => true,
                'access_level' => 'public',
                'tags' => 'policy, guidelines, company',
                'version' => $this->faker->randomFloat(1, 1.0, 3.5),
            ];
        });
    }

    /**
     * Expired document state
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => $this->faker->dateTimeBetween('-1 year', '-1 month'),
            'access_level' => 'restricted',
            'download_count' => $this->faker->numberBetween(0, 5), // Less downloads for expired
        ]);
    }

    /**
     * Recently uploaded state
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'download_count' => $this->faker->numberBetween(0, 10),
            'last_downloaded_at' => null,
        ]);
    }

    /**
     * Frequently accessed state
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'download_count' => $this->faker->numberBetween(50, 200),
            'last_downloaded_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'is_public' => true,
            'access_level' => 'public',
        ]);
    }

    /**
     * Large file state
     */
    public function large(): static
    {
        return $this->state(function (array $attributes) {
            $extension = $this->faker->randomElement(['mp4', 'zip', 'psd', 'avi']);
            return [
                'file_extension' => $extension,
                'mime_type' => $this->getMimeType($extension),
                'file_size' => $this->faker->numberBetween(50 * 1024 * 1024, 500 * 1024 * 1024), // 50MB - 500MB
                'file_name' => 'Large_File_' . $this->faker->numerify('###') . '.' . $extension,
            ];
        });
    }

    /**
     * Password protected state
     */
    public function passwordProtected(): static
    {
        return $this->state(fn (array $attributes) => [
            'password_protected' => true,
            'is_confidential' => true,
            'access_level' => 'restricted',
            'download_count' => $this->faker->numberBetween(0, 15), // Limited access
        ]);
    }

    /**
     * Project related document state
     */
    public function projectDocument(): static
    {
        return $this->state(fn (array $attributes) => [
            'project_id' => Project::factory(),
            'category' => $this->faker->randomElement(['report', 'presentation', 'spreadsheet']),
            'access_level' => 'internal',
            'tags' => 'project, documentation, deliverable',
        ]);
    }

    /**
     * Employee related document state
     */
    public function employeeDocument(): static
    {
        return $this->state(fn (array $attributes) => [
            'employee_id' => Employee::factory(),
            'category' => $this->faker->randomElement(['certificate', 'contract', 'form']),
            'access_level' => 'confidential',
            'is_confidential' => true,
            'tags' => 'employee, personal, hr',
        ]);
    }

    /**
     * Archive document state
     */
    public function archive(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'category' => 'archive',
                'file_extension' => 'zip',
                'mime_type' => 'application/zip',
                'file_name' => 'Archive_' . date('Y-m') . '_' . $this->faker->numerify('###') . '.zip',
                'file_size' => $this->faker->numberBetween(10 * 1024 * 1024, 100 * 1024 * 1024),
                'description' => 'Archived documents and files for backup purposes',
            ];
        });
    }
}