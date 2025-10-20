<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Employee;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;
use Carbon\Carbon;

class FileUploadController extends Controller
{
    /**
     * Maximum file size in KB
     */
    private const MAX_FILE_SIZE = 10240; // 10MB

    /**
     * Allowed file types
     */
    private const ALLOWED_TYPES = [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'],
        'archive' => ['zip', 'rar', '7z'],
        'video' => ['mp4', 'avi', 'mov', 'wmv'],
        'audio' => ['mp3', 'wav', 'ogg'],
    ];

    /**
     * Upload single file
     */
    public function upload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => [
                'required',
                'file',
                File::default()
                    ->max(self::MAX_FILE_SIZE)
                    ->types($this->getAllowedMimeTypes()),
            ],
            'type' => 'required|in:employee_photo,document,project_attachment,timesheet_attachment',
            'related_id' => 'nullable|integer',
            'description' => 'nullable|string|max:500',
            'is_public' => 'boolean',
            'category' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dosya yükleme hatası.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $file = $request->file('file');
        $type = $request->input('type');
        $relatedId = $request->input('related_id');

        // Validate permissions
        $this->validateUploadPermissions($type, $relatedId);

        try {
            $uploadResult = $this->processFileUpload($file, $type, $relatedId, [
                'description' => $request->input('description'),
                'is_public' => $request->boolean('is_public', false),
                'category' => $request->input('category'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Dosya başarıyla yüklendi.',
                'file' => $uploadResult,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dosya yüklenirken hata oluştu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload multiple files
     */
    public function uploadMultiple(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'files' => 'required|array|max:10',
            'files.*' => [
                'required',
                'file',
                File::default()
                    ->max(self::MAX_FILE_SIZE)
                    ->types($this->getAllowedMimeTypes()),
            ],
            'type' => 'required|in:employee_photo,document,project_attachment,timesheet_attachment',
            'related_id' => 'nullable|integer',
            'description' => 'nullable|string|max:500',
            'is_public' => 'boolean',
            'category' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dosya yükleme hatası.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $files = $request->file('files');
        $type = $request->input('type');
        $relatedId = $request->input('related_id');

        // Validate permissions
        $this->validateUploadPermissions($type, $relatedId);

        $uploadedFiles = [];
        $errors = [];

        foreach ($files as $index => $file) {
            try {
                $uploadResult = $this->processFileUpload($file, $type, $relatedId, [
                    'description' => $request->input('description'),
                    'is_public' => $request->boolean('is_public', false),
                    'category' => $request->input('category'),
                    'batch_upload' => true,
                ]);

                $uploadedFiles[] = $uploadResult;

            } catch (\Exception $e) {
                $errors[] = [
                    'file_index' => $index,
                    'filename' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($uploadedFiles) . ' dosya yüklendi' . 
                        (count($errors) > 0 ? ', ' . count($errors) . ' dosya hatalı.' : '.'),
            'uploaded_files' => $uploadedFiles,
            'errors' => $errors,
            'total_uploaded' => count($uploadedFiles),
            'total_errors' => count($errors),
        ]);
    }

    /**
     * Upload file via base64 (for mobile apps)
     */
    public function uploadBase64(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file_data' => 'required|string',
            'filename' => 'required|string|max:255',
            'mime_type' => 'required|string',
            'type' => 'required|in:employee_photo,document,project_attachment,timesheet_attachment',
            'related_id' => 'nullable|integer',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Base64 dosya yükleme hatası.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $type = $request->input('type');
        $relatedId = $request->input('related_id');

        // Validate permissions
        $this->validateUploadPermissions($type, $relatedId);

        try {
            // Decode base64 data
            $fileData = base64_decode($request->input('file_data'));
            $filename = $request->input('filename');
            $mimeType = $request->input('mime_type');

            // Validate file size
            $fileSize = strlen($fileData);
            if ($fileSize > self::MAX_FILE_SIZE * 1024) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dosya boyutu çok büyük. Maksimum: ' . self::MAX_FILE_SIZE . 'KB',
                ], 422);
            }

            // Validate file type
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            if (!$this->isAllowedExtension($extension)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Desteklenmeyen dosya türü: ' . $extension,
                ], 422);
            }

            $uploadResult = $this->processBase64Upload($fileData, $filename, $mimeType, $type, $relatedId, [
                'description' => $request->input('description'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Dosya başarıyla yüklendi.',
                'file' => $uploadResult,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dosya yüklenirken hata oluştu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get file info
     */
    public function getFileInfo(Request $request, string $id): JsonResponse
    {
        $document = Document::findOrFail($id);

        // Check permissions
        $this->validateFileAccess($document);

        return response()->json([
            'success' => true,
            'file' => [
                'id' => $document->id,
                'filename' => $document->filename,
                'original_filename' => $document->original_filename,
                'file_size' => $document->file_size,
                'mime_type' => $document->mime_type,
                'file_extension' => $document->file_extension,
                'description' => $document->description,
                'category' => $document->category,
                'upload_date' => $document->created_at->toISOString(),
                'uploader' => $document->uploader ? [
                    'id' => $document->uploader->id,
                    'name' => $document->uploader->name,
                ] : null,
                'download_count' => $document->download_count,
                'is_public' => $document->is_public,
                'url' => $document->getFileUrl(),
            ],
        ]);
    }

    /**
     * Delete uploaded file
     */
    public function delete(string $id): JsonResponse
    {
        $document = Document::findOrFail($id);

        // Check permissions
        $this->validateFileAccess($document);

        try {
            // Delete physical file
            if (Storage::exists($document->file_path)) {
                Storage::delete($document->file_path);
            }

            // Delete thumbnail if exists
            if ($document->thumbnail_path && Storage::exists($document->thumbnail_path)) {
                Storage::delete($document->thumbnail_path);
            }

            // Delete database record
            $document->delete();

            return response()->json([
                'success' => true,
                'message' => 'Dosya başarıyla silindi.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dosya silinirken hata oluştu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download file
     */
    public function download(string $id)
    {
        $document = Document::findOrFail($id);

        // Check permissions
        $this->validateFileAccess($document);

        // Check if file exists
        if (!Storage::exists($document->file_path)) {
            abort(404, 'Dosya bulunamadı.');
        }

        // Update download statistics
        $document->increment('download_count');
        $document->update([
            'last_downloaded_at' => now(),
            'last_downloaded_by' => Auth::id(),
        ]);

        return Storage::download(
            $document->file_path,
            $document->original_filename,
            [
                'Content-Type' => $document->mime_type,
            ]
        );
    }

    /**
     * Generate thumbnail for image files
     */
    public function generateThumbnail(string $id): JsonResponse
    {
        $document = Document::findOrFail($id);

        // Check permissions
        $this->validateFileAccess($document);

        // Check if file is an image
        if (!str_starts_with($document->mime_type, 'image/')) {
            return response()->json([
                'success' => false,
                'message' => 'Thumbnail sadece resim dosyaları için oluşturulabilir.',
            ], 422);
        }

        try {
            $thumbnailPath = $this->createThumbnail($document);

            $document->update(['thumbnail_path' => $thumbnailPath]);

            return response()->json([
                'success' => true,
                'message' => 'Thumbnail başarıyla oluşturuldu.',
                'thumbnail_url' => Storage::url($thumbnailPath),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Thumbnail oluşturulurken hata oluştu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get upload progress (for large files)
     */
    public function getUploadProgress(Request $request): JsonResponse
    {
        $uploadId = $request->input('upload_id');
        
        if (!$uploadId) {
            return response()->json([
                'success' => false,
                'message' => 'Upload ID gerekli.',
            ], 422);
        }

        // Get progress from cache
        $progress = cache()->get("upload_progress_{$uploadId}", [
            'uploaded' => 0,
            'total' => 0,
            'percentage' => 0,
            'status' => 'unknown',
        ]);

        return response()->json([
            'success' => true,
            'progress' => $progress,
        ]);
    }

    /**
     * Validate file signature (security check)
     */
    public function validateFileSignature(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $file = $request->file('file');
        
        try {
            $isValid = $this->checkFileSignature($file);
            $realMimeType = $this->getActualMimeType($file);

            return response()->json([
                'success' => true,
                'is_valid' => $isValid,
                'detected_mime_type' => $realMimeType,
                'claimed_mime_type' => $file->getMimeType(),
                'extension' => $file->getClientOriginalExtension(),
                'size' => $file->getSize(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dosya doğrulama hatası: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Scan file for viruses (if antivirus is available)
     */
    public function scanFile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $file = $request->file('file');

        try {
            $scanResult = $this->performVirusScan($file);

            return response()->json([
                'success' => true,
                'scan_result' => $scanResult,
                'is_clean' => $scanResult['is_clean'],
                'threats_found' => $scanResult['threats'] ?? [],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Virüs tarama hatası: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Private helper methods

    private function processFileUpload(UploadedFile $file, string $type, ?int $relatedId, array $options = []): array
    {
        // Generate unique filename
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $filename = time() . '_' . Str::random(10) . '.' . $extension;

        // Determine storage path based on type
        $storagePath = $this->getStoragePath($type);
        $fullPath = $storagePath . '/' . $filename;

        // Store file
        $file->storeAs($storagePath, $filename, 'public');

        // Create document record
        $documentData = [
            'filename' => $filename,
            'original_filename' => $originalName,
            'file_path' => $fullPath,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'file_extension' => $extension,
            'file_hash' => hash_file('sha256', $file->path()),
            'upload_type' => $type,
            'related_id' => $relatedId,
            'description' => $options['description'] ?? null,
            'category' => $options['category'] ?? null,
            'is_public' => $options['is_public'] ?? false,
            'uploaded_by' => Auth::id(),
            'status' => 'active',
        ];

        // Handle specific types
        if ($type === 'employee_photo') {
            $documentData['employee_id'] = $relatedId;
        } elseif ($type === 'project_attachment') {
            $documentData['project_id'] = $relatedId;
        }

        $document = Document::create($documentData);

        // Generate thumbnail for images
        if (str_starts_with($document->mime_type, 'image/')) {
            try {
                $thumbnailPath = $this->createThumbnail($document);
                $document->update(['thumbnail_path' => $thumbnailPath]);
            } catch (\Exception $e) {
                // Thumbnail creation failed, but file upload succeeded
            }
        }

        return [
            'id' => $document->id,
            'filename' => $document->filename,
            'original_filename' => $document->original_filename,
            'file_size' => $document->file_size,
            'mime_type' => $document->mime_type,
            'url' => Storage::url($document->file_path),
            'thumbnail_url' => $document->thumbnail_path ? Storage::url($document->thumbnail_path) : null,
        ];
    }

    private function processBase64Upload(string $fileData, string $filename, string $mimeType, string $type, ?int $relatedId, array $options = []): array
    {
        // Generate unique filename
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $uniqueFilename = time() . '_' . Str::random(10) . '.' . $extension;

        // Determine storage path
        $storagePath = $this->getStoragePath($type);
        $fullPath = $storagePath . '/' . $uniqueFilename;

        // Store file
        Storage::put($fullPath, $fileData, 'public');

        // Create document record
        $documentData = [
            'filename' => $uniqueFilename,
            'original_filename' => $filename,
            'file_path' => $fullPath,
            'file_size' => strlen($fileData),
            'mime_type' => $mimeType,
            'file_extension' => $extension,
            'file_hash' => hash('sha256', $fileData),
            'upload_type' => $type,
            'related_id' => $relatedId,
            'description' => $options['description'] ?? null,
            'uploaded_by' => Auth::id(),
            'status' => 'active',
        ];

        $document = Document::create($documentData);

        return [
            'id' => $document->id,
            'filename' => $document->filename,
            'original_filename' => $document->original_filename,
            'file_size' => $document->file_size,
            'mime_type' => $document->mime_type,
            'url' => Storage::url($document->file_path),
        ];
    }

    private function validateUploadPermissions(string $type, ?int $relatedId): void
    {
        $user = Auth::user();

        switch ($type) {
            case 'employee_photo':
                if ($relatedId) {
                    $employee = Employee::find($relatedId);
                    if (!$employee) {
                        abort(404, 'Çalışan bulunamadı.');
                    }
                    
                    // Users can only upload their own photo unless they're admin/manager
                    if (!$user->hasAnyRole(['admin', 'project_manager', 'site_manager']) && 
                        $user->employee->id !== $employee->id) {
                        abort(403, 'Bu çalışan için fotoğraf yükleme yetkiniz yok.');
                    }
                }
                break;

            case 'project_attachment':
                if ($relatedId) {
                    $project = Project::find($relatedId);
                    if (!$project) {
                        abort(404, 'Proje bulunamadı.');
                    }
                    
                    // Check project access
                    if (!$user->hasAnyRole(['admin', 'project_manager']) && 
                        $project->site_manager_id !== $user->employee->id) {
                        abort(403, 'Bu proje için dosya yükleme yetkiniz yok.');
                    }
                }
                break;

            case 'document':
                // General document upload - check if user has document management permission
                if (!$user->hasAnyRole(['admin', 'project_manager', 'site_manager'])) {
                    abort(403, 'Belge yükleme yetkiniz yok.');
                }
                break;
        }
    }

    private function validateFileAccess(Document $document): void
    {
        $user = Auth::user();

        // Public documents are accessible to everyone
        if ($document->is_public) {
            return;
        }

        // Admins and project managers can access all files
        if ($user->hasAnyRole(['admin', 'project_manager'])) {
            return;
        }

        // File uploader can always access their files
        if ($document->uploaded_by === $user->id) {
            return;
        }

        // Check specific access rules
        switch ($document->upload_type) {
            case 'employee_photo':
                // Users can view their own photos and photos of employees in their projects
                if ($document->employee_id === $user->employee->id) {
                    return;
                }
                
                if ($user->hasRole('site_manager')) {
                    $managedProjects = Project::where('site_manager_id', $user->employee->id)->pluck('id');
                    $employee = Employee::find($document->employee_id);
                    if ($employee && $employee->projects()->whereIn('projects.id', $managedProjects)->exists()) {
                        return;
                    }
                }
                break;

            case 'project_attachment':
                // Check if user has access to the project
                if ($document->project_id) {
                    $project = Project::find($document->project_id);
                    if ($project && ($project->site_manager_id === $user->employee->id || 
                        $user->employee->projects()->where('projects.id', $project->id)->exists())) {
                        return;
                    }
                }
                break;
        }

        abort(403, 'Bu dosyaya erişim yetkiniz yok.');
    }

    private function getStoragePath(string $type): string
    {
        switch ($type) {
            case 'employee_photo':
                return 'uploads/employees/photos';
            case 'project_attachment':
                return 'uploads/projects/attachments';
            case 'timesheet_attachment':
                return 'uploads/timesheets';
            case 'document':
                return 'uploads/documents';
            default:
                return 'uploads/misc';
        }
    }

    private function getAllowedMimeTypes(): array
    {
        $mimeTypes = [];
        
        foreach (self::ALLOWED_TYPES as $category => $extensions) {
            foreach ($extensions as $ext) {
                switch ($ext) {
                    case 'jpg':
                    case 'jpeg':
                        $mimeTypes[] = 'image/jpeg';
                        break;
                    case 'png':
                        $mimeTypes[] = 'image/png';
                        break;
                    case 'gif':
                        $mimeTypes[] = 'image/gif';
                        break;
                    case 'webp':
                        $mimeTypes[] = 'image/webp';
                        break;
                    case 'pdf':
                        $mimeTypes[] = 'application/pdf';
                        break;
                    case 'doc':
                        $mimeTypes[] = 'application/msword';
                        break;
                    case 'docx':
                        $mimeTypes[] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                        break;
                    case 'xls':
                        $mimeTypes[] = 'application/vnd.ms-excel';
                        break;
                    case 'xlsx':
                        $mimeTypes[] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                        break;
                    case 'ppt':
                        $mimeTypes[] = 'application/vnd.ms-powerpoint';
                        break;
                    case 'pptx':
                        $mimeTypes[] = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
                        break;
                    case 'txt':
                        $mimeTypes[] = 'text/plain';
                        break;
                    case 'zip':
                        $mimeTypes[] = 'application/zip';
                        break;
                    case 'mp4':
                        $mimeTypes[] = 'video/mp4';
                        break;
                    case 'mp3':
                        $mimeTypes[] = 'audio/mpeg';
                        break;
                }
            }
        }

        return array_unique($mimeTypes);
    }

    private function isAllowedExtension(string $extension): bool
    {
        $extension = strtolower($extension);
        
        foreach (self::ALLOWED_TYPES as $types) {
            if (in_array($extension, $types)) {
                return true;
            }
        }
        
        return false;
    }

    private function createThumbnail(Document $document): string
    {
        if (!extension_loaded('gd')) {
            throw new \Exception('GD extension gerekli.');
        }

        $sourcePath = Storage::path($document->file_path);
        $thumbnailPath = 'uploads/thumbnails/' . pathinfo($document->filename, PATHINFO_FILENAME) . '_thumb.jpg';
        $fullThumbnailPath = Storage::path($thumbnailPath);

        // Create thumbnail directory if it doesn't exist
        $thumbnailDir = dirname($fullThumbnailPath);
        if (!file_exists($thumbnailDir)) {
            mkdir($thumbnailDir, 0755, true);
        }

        // Create thumbnail based on image type
        $imageInfo = getimagesize($sourcePath);
        $sourceWidth = $imageInfo[0];
        $sourceHeight = $imageInfo[1];
        $mimeType = $imageInfo['mime'];

        // Calculate thumbnail dimensions (max 300x300)
        $maxDimension = 300;
        if ($sourceWidth > $sourceHeight) {
            $thumbWidth = $maxDimension;
            $thumbHeight = intval(($sourceHeight * $maxDimension) / $sourceWidth);
        } else {
            $thumbHeight = $maxDimension;
            $thumbWidth = intval(($sourceWidth * $maxDimension) / $sourceHeight);
        }

        // Create source image
        switch ($mimeType) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            default:
                throw new \Exception('Desteklenmeyen resim formatı.');
        }

        // Create thumbnail
        $thumbnailImage = imagecreatetruecolor($thumbWidth, $thumbHeight);
        imagecopyresampled($thumbnailImage, $sourceImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $sourceWidth, $sourceHeight);

        // Save thumbnail
        imagejpeg($thumbnailImage, $fullThumbnailPath, 85);

        // Clean up
        imagedestroy($sourceImage);
        imagedestroy($thumbnailImage);

        return $thumbnailPath;
    }

    private function checkFileSignature(UploadedFile $file): bool
    {
        $signatures = [
            'jpg' => ["\xFF\xD8\xFF"],
            'png' => ["\x89\x50\x4E\x47\x0D\x0A\x1A\x0A"],
            'gif' => ["GIF87a", "GIF89a"],
            'pdf' => ["%PDF"],
            'zip' => ["\x50\x4B\x03\x04", "\x50\x4B\x05\x06", "\x50\x4B\x07\x08"],
        ];

        $extension = strtolower($file->getClientOriginalExtension());
        
        if (!isset($signatures[$extension])) {
            return true; // No signature check for this type
        }

        $fileHandle = fopen($file->path(), 'rb');
        $fileHeader = fread($fileHandle, 12);
        fclose($fileHandle);

        foreach ($signatures[$extension] as $signature) {
            if (strpos($fileHeader, $signature) === 0) {
                return true;
            }
        }

        return false;
    }

    private function getActualMimeType(UploadedFile $file): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file->path());
        finfo_close($finfo);

        return $mimeType ?: 'application/octet-stream';
    }

    private function performVirusScan(UploadedFile $file): array
    {
        // This would integrate with an antivirus service
        // For demo purposes, return clean result
        return [
            'is_clean' => true,
            'scan_time' => now()->toISOString(),
            'engine' => 'demo',
            'threats' => [],
        ];
    }
}