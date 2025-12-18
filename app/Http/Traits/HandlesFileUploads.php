<?php

namespace App\Http\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HandlesFileUploads
{
    /**
     * Set file permissions to 644 (file) and 755 (directory).
     * This allows web server to read files while maintaining security.
     *
     * @param string $relativePath
     * @param string $disk
     * @return void
     */
    protected function setFilePermissions(string $relativePath, string $disk = 'public'): void
    {
        try {
            $fullPath = Storage::disk($disk)->path($relativePath);

            // Set permission for the file (0644) - readable by web server
            if (file_exists($fullPath) && is_file($fullPath)) {
                chmod($fullPath, 0644);
            }

            // Set permission for the directory containing the file (0755) - accessible by web server
            $directory = dirname($fullPath);
            if (is_dir($directory)) {
                chmod($directory, 0755);
            }
        } catch (\Exception $e) {
            // Silently fail if permission setting fails
        }
    }

    /**
     * Upload a single file to storage.
     *
     * @param UploadedFile|null $file
     * @param string $directory
     * @param string $disk
     * @return string|null The storage path (e.g., '/storage/houses/image.jpg')
     */
    protected function uploadFile(?UploadedFile $file, string $directory, string $disk = 'public'): ?string
    {
        if (!$file || !$file->isValid()) {
            return null;
        }

        $path = $file->store($directory, $disk);

        // Set permissions (0644 for file, 0755 for directory) for file and directory
        $this->setFilePermissions($path, $disk);

        return '/storage/' . $path;
    }

    /**
     * Upload multiple files to storage.
     *
     * @param array $files
     * @param string $directory
     * @param string $disk
     * @return array Array of storage paths
     */
    protected function uploadFiles(array $files, string $directory, string $disk = 'public'): array
    {
        $paths = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $path = $file->store($directory, $disk);

                // Set permissions (0644 for file, 0755 for directory) for file and directory
                $this->setFilePermissions($path, $disk);

                $paths[] = '/storage/' . $path;
            }
        }

        return $paths;
    }

    /**
     * Delete a file from storage.
     *
     * @param string|null $filePath The full path (e.g., '/storage/houses/image.jpg')
     * @param string $disk
     * @return bool
     */
    protected function deleteFile(?string $filePath, string $disk = 'public'): bool
    {
        if (!$filePath) {
            return false;
        }

        // Remove '/storage/' prefix to get the relative path
        $relativePath = str_replace('/storage/', '', $filePath);

        if (Storage::disk($disk)->exists($relativePath)) {
            return Storage::disk($disk)->delete($relativePath);
        }

        return false;
    }

    /**
     * Delete multiple files from storage.
     *
     * @param array $filePaths Array of full paths
     * @param string $disk
     * @return void
     */
    protected function deleteFiles(array $filePaths, string $disk = 'public'): void
    {
        foreach ($filePaths as $filePath) {
            $this->deleteFile($filePath, $disk);
        }
    }

    /**
     * Replace an old file with a new one.
     * Deletes the old file and uploads the new one.
     *
     * @param UploadedFile|null $newFile
     * @param string|null $oldFilePath
     * @param string $directory
     * @param string $disk
     * @return string|null The new storage path
     */
    protected function replaceFile(?UploadedFile $newFile, ?string $oldFilePath, string $directory, string $disk = 'public'): ?string
    {
        // Delete old file if exists
        if ($oldFilePath) {
            $this->deleteFile($oldFilePath, $disk);
        }

        // Upload new file
        return $this->uploadFile($newFile, $directory, $disk);
    }
}
