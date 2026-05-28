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
        $fileName .= '-' . Str::lower(Str::random(8)) . '.' . $file->getClientOriginalExtension();

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

            MediaLibrary::query()->create([
                'uploaded_by' => Auth::id(),
                'title' => $title,
                'file_name' => basename($path),
                'original_name' => $file->getClientOriginalName(),
                'disk' => 'public',
                'file_path' => $path,
                'file_url' => $url,
                'folder' => $safeFolder,
                'mime_type' => $file->getClientMimeType(),
                'extension' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize() ?: 0,
                'is_active' => true,
            ]);

            return $url;
        } catch (Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                $errorField => 'File upload failed on the server. Please try again, or use a smaller PNG/JPG/SVG/WEBP/ICO file.',
            ]);
        }
    }
}
