<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\MediaLibrary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

trait HandlesAdminUploads
{
    protected function storeAdminUpload(
        UploadedFile $file,
        string $folder,
        ?string $title = null,
        string $errorField = 'upload'
    ): string
    {
        $safeFolder = trim($folder, '/');
        $targetDirectory = 'admin/' . $safeFolder;
            $fileName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
            $fileName = $fileName !== '' ? $fileName : 'upload';
            $fileName = Str::limit($fileName, 120, '');
            $extension = Str::lower(substr((string) $file->getClientOriginalExtension(), 0, 20));
            $fileName .= '-' . Str::lower(Str::random(8)) . ($extension !== '' ? '.' . $extension : '');

        try {
            $disk = Storage::disk('public');

            if (! $disk->exists($targetDirectory) && ! $disk->makeDirectory($targetDirectory)) {
                throw new \RuntimeException('Unable to create upload directory.');
            }

            $path = $disk->putFileAs($targetDirectory, $file, $fileName);

            if (! is_string($path) || $path === '') {
                throw new \RuntimeException('Unable to store uploaded file.');
            }

            $url = $disk->url($path);

            try {
                MediaLibrary::query()->create([
                    'uploaded_by' => Auth::id(),
                    'title' => $title ? Str::limit($title, 180, '') : null,
                    'file_name' => Str::limit(basename($path), 255, ''),
                    'original_name' => Str::limit($file->getClientOriginalName(), 255, ''),
                    'disk' => 'public',
                    'file_path' => Str::limit($path, 255, ''),
                    'file_url' => Str::limit($url, 255, ''),
                    'folder' => Str::limit($safeFolder, 120, ''),
                    'mime_type' => Str::limit((string) $file->getClientMimeType(), 120, ''),
                    'extension' => $extension !== '' ? $extension : null,
                    'file_size' => $file->getSize() ?: 0,
                    'is_active' => true,
                ]);
            } catch (Throwable $mediaException) {
                report($mediaException);
            }

            return $url;
        } catch (Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                $errorField => 'File upload failed on the server. Please try again, or use a smaller PNG/JPG/SVG/WEBP/ICO file.',
            ]);
        }
    }
}
