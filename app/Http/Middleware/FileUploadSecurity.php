<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class FileUploadSecurity
{
    /**
     * Allowed file types by category
     */
    protected array $allowedTypes = [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
        'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf'],
        'archive' => ['zip', 'rar', '7z', 'tar', 'gz'],
        'cad' => ['dwg', 'dxf', 'step', 'iges'],
        'media' => ['mp4', 'avi', 'mov', 'mp3', 'wav'],
    ];

    /**
     * Max file sizes by type (in MB)
     */
    protected array $maxSizes = [
        'image' => 10,      // 10MB
        'document' => 50,   // 50MB
        'archive' => 100,   // 100MB
        'cad' => 200,       // 200MB for CAD files
        'media' => 500,     // 500MB for videos
    ];

    /**
     * Dangerous file extensions that are never allowed
     */
    protected array $dangerousExtensions = [
        'exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js', 'jar',
        'php', 'php3', 'php4', 'php5', 'phtml', 'asp', 'aspx', 'jsp'
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $category = 'document'): Response
    {
        // Dosya yükleme işlemi var mı?
        if (!$request->hasFile('file') && !$request->hasFile('files')) {
            return $next($request);
        }

        $files = $request->hasFile('files') ? $request->file('files') : [$request->file('file')];

        foreach ($files as $file) {
            if (!$file) continue;

            // Dosya validasyonu
            $validation = $this->validateFile($file, $category);
            if (!$validation['valid']) {
                return response()->json([
                    'message' => 'Dosya güvenlik kontrolünden geçemedi.',
                    'error' => $validation['error'],
                    'file_name' => $file->getClientOriginalName()
                ], 400);
            }
        }

        // Kullanıcı quota kontrolü
        if (Auth::check()) {
            $quotaCheck = $this->checkUserQuota(Auth::user(), $files);
            if (!$quotaCheck['valid']) {
                return response()->json([
                    'message' => 'Dosya yükleme kotanız aşıldı.',
                    'error' => $quotaCheck['error']
                ], 400);
            }
        }

        return $next($request);
    }

    /**
     * Dosyayı doğrula
     */
    private function validateFile($file, string $category): array
    {
        $fileName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType();

        // Dosya ismi kontrolü
        if (empty($fileName) || strlen($fileName) > 255) {
            return [
                'valid' => false,
                'error' => 'Dosya ismi geçersiz veya çok uzun.'
            ];
        }

        // Tehlikeli karakterler
        if (preg_match('/[<>:"|?*\\\\\/]/', $fileName)) {
            return [
                'valid' => false,
                'error' => 'Dosya isminde geçersiz karakterler bulunuyor.'
            ];
        }

        // Tehlikeli uzantılar
        if (in_array($extension, $this->dangerousExtensions)) {
            return [
                'valid' => false,
                'error' => "'{$extension}' uzantılı dosyalar güvenlik nedeniyle yüklenemez."
            ];
        }

        // Kategori bazlı uzantı kontrolü
        if (!in_array($extension, $this->allowedTypes[$category] ?? [])) {
            return [
                'valid' => false,
                'error' => "'{$extension}' uzantısı '{$category}' kategorisi için izin verilmez.",
                'allowed_types' => $this->allowedTypes[$category] ?? []
            ];
        }

        // Dosya boyutu kontrolü
        $maxSize = ($this->maxSizes[$category] ?? 10) * 1024 * 1024; // MB to bytes
        if ($fileSize > $maxSize) {
            return [
                'valid' => false,
                'error' => "Dosya boyutu çok büyük. Maksimum: " . ($this->maxSizes[$category] ?? 10) . "MB"
            ];
        }

        // MIME type kontrolü
        if (!$this->isValidMimeType($mimeType, $extension)) {
            return [
                'valid' => false,
                'error' => 'Dosya içeriği uzantısı ile uyuşmuyor.'
            ];
        }

        // Dosya içeriği güvenlik kontrolü
        $contentCheck = $this->checkFileContent($file);
        if (!$contentCheck['valid']) {
            return $contentCheck;
        }

        return ['valid' => true];
    }

    /**
     * MIME type kontrolü
     */
    private function isValidMimeType(string $mimeType, string $extension): bool
    {
        $validMimes = [
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'webp' => ['image/webp'],
            'svg' => ['image/svg+xml'],
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'xls' => ['application/vnd.ms-excel'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'ppt' => ['application/vnd.ms-powerpoint'],
            'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation'],
            'txt' => ['text/plain'],
            'zip' => ['application/zip'],
            'rar' => ['application/x-rar-compressed'],
        ];

        if (!isset($validMimes[$extension])) {
            return true; // Extension not in our list, allow it
        }

        return in_array($mimeType, $validMimes[$extension]);
    }

    /**
     * Dosya içeriği güvenlik kontrolü
     */
    private function checkFileContent($file): array
    {
        $filePath = $file->getRealPath();

        // Dosya okunamıyorsa
        if (!is_readable($filePath)) {
            return [
                'valid' => false,
                'error' => 'Dosya okunamıyor.'
            ];
        }

        // İlk 512 byte'ı kontrol et (magic bytes)
        $handle = fopen($filePath, 'rb');
        $firstBytes = fread($handle, 512);
        fclose($handle);

        // PHP kodları arama
        $phpPatterns = [
            '/<\?php/i',
            '/<\?=/i',
            '/<script[\s>]/i',
            '/eval\s*\(/i',
            '/base64_decode/i',
            '/exec\s*\(/i',
            '/system\s*\(/i',
            '/passthru\s*\(/i',
            '/shell_exec\s*\(/i',
        ];

        foreach ($phpPatterns as $pattern) {
            if (preg_match($pattern, $firstBytes)) {
                return [
                    'valid' => false,
                    'error' => 'Dosya içeriğinde tehlikeli kod tespit edildi.'
                ];
            }
        }

        return ['valid' => true];
    }

    /**
     * Kullanıcı quota kontrolü
     */
    private function checkUserQuota($user, array $files): array
    {
        // Kullanıcının toplam dosya boyutunu hesapla
        $totalSize = 0;
        foreach ($files as $file) {
            if ($file) {
                $totalSize += $file->getSize();
            }
        }

        // Kullanıcının mevcut toplam dosya boyutunu al (Document modeli üzerinden)
        $userCurrentSize = \App\Models\Document::where('uploaded_by', $user->id)
            ->sum('file_size');

        // Maksimum quota (varsayılan: 5GB)
        $maxQuota = 5 * 1024 * 1024 * 1024; // 5GB in bytes

        // Admin kullanıcılar için sınırsız
        if ($user->hasRole('admin') || $user->hasRole('super_admin')) {
            return ['valid' => true];
        }

        // Quota kontrolü
        if (($userCurrentSize + $totalSize) > $maxQuota) {
            $remainingMB = round(($maxQuota - $userCurrentSize) / (1024 * 1024), 2);
            return [
                'valid' => false,
                'error' => "Dosya yükleme kotanız aşıldı. Kalan: {$remainingMB}MB"
            ];
        }

        return ['valid' => true];
    }
}