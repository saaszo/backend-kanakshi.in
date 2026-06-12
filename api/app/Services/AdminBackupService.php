<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use ZipArchive;

class AdminBackupService
{
    private const BACKUP_DISK_DIRECTORY = 'app/admin-backups';

    public function createBackupArchive(?string $label = null): array
    {
        $timestamp = now()->format('Ymd-His');
        $slug = $label ? Str::slug($label) : 'manual-backup';
        $fileName = "little-divinity-backup-{$timestamp}-{$slug}.zip";
        $relativePath = self::BACKUP_DISK_DIRECTORY.'/'.$fileName;
        $absolutePath = storage_path($relativePath);

        File::ensureDirectoryExists(dirname($absolutePath));

        $zip = new ZipArchive();
        if ($zip->open($absolutePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Unable to create backup archive.');
        }

        $manifest = $this->buildManifest($label);
        $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $databasePath = $this->databasePath();
        if ($databasePath !== null && File::exists($databasePath)) {
            $zip->addFile($databasePath, 'database/database.sqlite');
        }

        $exports = $this->collectStructuredExports();
        foreach ($exports as $name => $rows) {
            $zip->addFromString(
                'exports/'.$name.'.json',
                json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );
        }

        $this->addDirectoryToZip($zip, storage_path('app/public'), 'storage/app/public');

        $zip->close();

        return [
            'file_name' => $fileName,
            'absolute_path' => $absolutePath,
            'relative_path' => $relativePath,
        ];
    }

    public function restoreBackupArchive(string $uploadedArchivePath): void
    {
        $workingDirectory = storage_path('app/tmp/admin-restore-'.Str::uuid());
        File::ensureDirectoryExists($workingDirectory);

        $zip = new ZipArchive();
        if ($zip->open($uploadedArchivePath) !== true) {
            throw new \RuntimeException('Uploaded backup archive could not be opened.');
        }

        if ($zip->locateName('manifest.json') === false) {
            $zip->close();
            File::deleteDirectory($workingDirectory);
            throw new \RuntimeException('This backup file is missing its manifest and cannot be restored safely.');
        }

        if (! $zip->extractTo($workingDirectory)) {
            $zip->close();
            File::deleteDirectory($workingDirectory);
            throw new \RuntimeException('Backup archive could not be extracted on the server.');
        }

        $zip->close();

        try {
            $databasePath = $this->databasePath();
            $restoredDatabasePath = $workingDirectory.'/database/database.sqlite';
            $restoredPublicPath = $workingDirectory.'/storage/app/public';

            DB::disconnect();

            if ($databasePath !== null && File::exists($restoredDatabasePath)) {
                File::ensureDirectoryExists(dirname($databasePath));
                File::copy($restoredDatabasePath, $databasePath);
            }

            if (File::isDirectory($restoredPublicPath)) {
                $targetPublicPath = storage_path('app/public');
                File::deleteDirectory($targetPublicPath);
                File::ensureDirectoryExists(dirname($targetPublicPath));
                File::copyDirectory($restoredPublicPath, $targetPublicPath);
            }

            DB::purge();
            DB::reconnect();
        } finally {
            File::deleteDirectory($workingDirectory);
        }
    }

    private function buildManifest(?string $label): array
    {
        return [
            'type' => 'little-divinity-admin-backup',
            'label' => $label ?: 'Manual backup',
            'created_at' => now()->toIso8601String(),
            'app_env' => app()->environment(),
            'database_connection' => DB::getDefaultConnection(),
            'database_driver' => DB::connection()->getDriverName(),
            'includes' => [
                'database/database.sqlite',
                'exports/*.json',
                'storage/app/public/**/*',
            ],
        ];
    }

    private function databasePath(): ?string
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            return null;
        }

        $configured = config('database.connections.sqlite.database');

        if (! is_string($configured) || trim($configured) === '' || $configured === ':memory:') {
            return null;
        }

        return $configured;
    }

    private function collectStructuredExports(): array
    {
        $exports = [];

        foreach ($this->backupTableNames() as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $exports[$table] = DB::table($table)->get()->map(function ($row) {
                return (array) $row;
            })->all();
        }

        return $exports;
    }

    private function backupTableNames(): array
    {
        return [
            'users',
            'store_settings',
            'settings',
            'social_links',
            'menu_items',
            'categories',
            'products',
            'product_variants',
            'coupons',
            'orders',
            'order_items',
            'order_tracking',
            'order_returns',
            'homepage_sections',
            'homepage_products',
            'payment_gateway_settings',
            'delivery_partner_settings',
            'blog_posts',
            'blog_categories',
            'blog_tags',
            'blog_authors',
            'registrations',
            'repair_claims',
            'buyback_requests',
        ];
    }

    private function addDirectoryToZip(ZipArchive $zip, string $sourceDirectory, string $zipDirectory): void
    {
        if (! File::isDirectory($sourceDirectory)) {
            return;
        }

        $zip->addEmptyDir($zipDirectory);

        foreach (File::allFiles($sourceDirectory) as $file) {
            $relativePath = ltrim(str_replace($sourceDirectory, '', $file->getPathname()), DIRECTORY_SEPARATOR);
            $zip->addFile($file->getPathname(), $zipDirectory.'/'.$relativePath);
        }
    }
}
