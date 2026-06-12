<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminBackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    public function __construct(
        private readonly AdminBackupService $backupService,
    ) {
    }

    public function download(): BinaryFileResponse
    {
        $backup = $this->backupService->createBackupArchive('dashboard-download');

        return response()->download($backup['absolute_path'], $backup['file_name'])->deleteFileAfterSend(true);
    }

    public function restore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'backup_file' => ['required', 'file', 'mimes:zip', 'max:204800'],
        ]);

        $uploadedFile = $validated['backup_file'];
        $uploadedPath = $uploadedFile->getRealPath();

        if (! is_string($uploadedPath) || $uploadedPath === '') {
            return back()->withErrors([
                'backup_file' => 'Uploaded backup file could not be read on the server.',
            ]);
        }

        try {
            $this->backupService->createBackupArchive('pre-restore-safety-net');
            $this->backupService->restoreBackupArchive($uploadedPath);
            Artisan::call('optimize:clear');
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'backup_file' => $exception->getMessage() ?: 'Backup restore failed.',
            ]);
        }

        return redirect()
            ->route('admin.dashboard')
            ->with('status', 'Backup restored successfully. A safety backup of the previous state was created first.');
    }
}
