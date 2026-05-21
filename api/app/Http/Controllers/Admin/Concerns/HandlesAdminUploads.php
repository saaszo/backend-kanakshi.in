<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\MediaLibrary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HandlesAdminUploads
{
    protected function storeAdminUpload(UploadedFile $file, string $folder, ?string $title = null): string
    {
        $safeFolder = trim($folder, '/');
        $fileName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $fileName = $fileName !== '' ? $fileName : 'upload';
        $fileName .= '-' . Str::lower(Str::random(8)) . '.' . $file->getClientOriginalExtension();

        $path = $file->storeAs('admin/' . $safeFolder, $fileName, 'public');
        $url = Storage::disk('public')->url($path);

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
    }
}
