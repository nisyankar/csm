<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FileUploadController extends Controller
{
    /**
     * Dosya yükle (Genel - Mobil için)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function upload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:10240', // Max 10MB
            'type' => 'required|in:image,document,avatar',
            'reference_type' => 'nullable|string', // e.g., 'project', 'material', 'timesheet'
            'reference_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $file = $request->file('file');
            $type = $request->input('type');

            // Dosya türüne göre klasör belirleme
            $folder = match ($type) {
                'image' => 'uploads/images',
                'document' => 'uploads/documents',
                'avatar' => 'uploads/avatars',
                default => 'uploads/general',
            };

            // Dosya adını oluştur (benzersiz)
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $filename = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '_' . time() . '.' . $extension;

            // Dosyayı kaydet
            $path = $file->storeAs($folder, $filename, 'public');

            return response()->json([
                'success' => true,
                'message' => 'Dosya başarıyla yüklendi.',
                'data' => [
                    'filename' => $filename,
                    'original_name' => $originalName,
                    'path' => $path,
                    'url' => Storage::url($path),
                    'full_url' => asset('storage/' . $path),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'extension' => $extension,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dosya yükleme sırasında bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Görsel yükle (Image - Mobil için)
     * Özel validasyon ve boyutlandırma ile
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // Max 5MB
            'reference_type' => 'nullable|string',
            'reference_id' => 'nullable|integer',
            'resize' => 'nullable|boolean', // Otomatik boyutlandırma
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $image = $request->file('image');
            $folder = 'uploads/images';

            // Dosya adını oluştur
            $filename = 'img_' . time() . '_' . Str::random(8) . '.' . $image->getClientOriginalExtension();

            // Dosyayı kaydet
            $path = $image->storeAs($folder, $filename, 'public');

            // TODO: Eğer resize true ise, Image Intervention ile boyutlandırma yapılabilir
            // if ($request->boolean('resize')) {
            //     // Image::make($image)->resize(1024, null, function ($constraint) {
            //     //     $constraint->aspectRatio();
            //     // })->save(storage_path('app/public/' . $path));
            // }

            return response()->json([
                'success' => true,
                'message' => 'Görsel başarıyla yüklendi.',
                'data' => [
                    'filename' => $filename,
                    'original_name' => $image->getClientOriginalName(),
                    'path' => $path,
                    'url' => Storage::url($path),
                    'full_url' => asset('storage/' . $path),
                    'size' => $image->getSize(),
                    'mime_type' => $image->getMimeType(),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Görsel yükleme sırasında bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Çoklu görsel yükle (Mobil için - Örn: Şantiye fotoğrafları)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadMultipleImages(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'images' => 'required|array|max:10', // Max 10 fotoğraf
            'images.*' => 'image|mimes:jpeg,png,jpg|max:5120', // Her biri max 5MB
            'reference_type' => 'nullable|string',
            'reference_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $uploadedFiles = [];
            $folder = 'uploads/images';

            foreach ($request->file('images') as $image) {
                $filename = 'img_' . time() . '_' . Str::random(8) . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs($folder, $filename, 'public');

                $uploadedFiles[] = [
                    'filename' => $filename,
                    'original_name' => $image->getClientOriginalName(),
                    'path' => $path,
                    'url' => Storage::url($path),
                    'full_url' => asset('storage/' . $path),
                    'size' => $image->getSize(),
                    'mime_type' => $image->getMimeType(),
                ];
            }

            return response()->json([
                'success' => true,
                'message' => count($uploadedFiles) . ' görsel başarıyla yüklendi.',
                'data' => $uploadedFiles,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Görseller yüklenirken bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Profil fotoğrafı yükle
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Max 2MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = $request->user();
            $avatar = $request->file('avatar');
            $folder = 'uploads/avatars';

            // Eski avatar'ı sil (varsa)
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Yeni avatar'ı kaydet
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $avatar->getClientOriginalExtension();
            $path = $avatar->storeAs($folder, $filename, 'public');

            // User'ı güncelle
            $user->update(['avatar' => $path]);

            return response()->json([
                'success' => true,
                'message' => 'Profil fotoğrafı başarıyla güncellendi.',
                'data' => [
                    'avatar_url' => Storage::url($path),
                    'full_url' => asset('storage/' . $path),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Profil fotoğrafı yüklenirken bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Dosya sil
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'path' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $path = $request->input('path');

            // Security check: Dosya sadece uploads klasöründe olmalı
            if (!str_starts_with($path, 'uploads/')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Geçersiz dosya yolu.',
                ], 400);
            }

            // Dosya var mı kontrol et
            if (!Storage::disk('public')->exists($path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dosya bulunamadı.',
                ], 404);
            }

            // Dosyayı sil
            Storage::disk('public')->delete($path);

            return response()->json([
                'success' => true,
                'message' => 'Dosya başarıyla silindi.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dosya silinirken bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Base64 görsel yükle (Mobil kamera için)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadBase64(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|string', // Base64 encoded image
            'reference_type' => 'nullable|string',
            'reference_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $base64Image = $request->input('image');

            // Base64'ü decode et
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
                $type = strtolower($type[1]); // jpg, png, gif

                $base64Image = str_replace(' ', '+', $base64Image);
                $imageData = base64_decode($base64Image);

                if ($imageData === false) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Base64 decode hatası.',
                    ], 400);
                }

                // Dosya adı oluştur
                $filename = 'img_' . time() . '_' . Str::random(8) . '.' . $type;
                $folder = 'uploads/images';
                $path = $folder . '/' . $filename;

                // Dosyayı kaydet
                Storage::disk('public')->put($path, $imageData);

                return response()->json([
                    'success' => true,
                    'message' => 'Görsel başarıyla yüklendi.',
                    'data' => [
                        'filename' => $filename,
                        'path' => $path,
                        'url' => Storage::url($path),
                        'full_url' => asset('storage/' . $path),
                    ],
                ], 201);
            }

            return response()->json([
                'success' => false,
                'message' => 'Geçersiz base64 formatı.',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Base64 görsel yüklenirken bir hata oluştu.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
