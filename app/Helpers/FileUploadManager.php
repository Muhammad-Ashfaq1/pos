<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadManager
{
    public static function uploadFile(UploadedFile $file, $file_path = null, $disk = 'public')
    {
        $file_path = $file_path ?? 'images/' . (auth()->id ?? '') . '/';
        $original_filename = $file->getClientOriginalName();
        $original_filename_arr = explode('.', $original_filename);
        $filename = $original_filename_arr[0];
        $file_ext = strtolower(end($original_filename_arr));
        $file_path_name = time() . '_' . Str::slug(pathinfo($original_filename, PATHINFO_FILENAME)) . '.' . $file_ext;

        // Use Storage::put to store the file on specified disk
        if (Storage::disk($disk)->put($file_path . $file_path_name, file_get_contents($file))) {
            $relative_path = $file_path . $file_path_name;
        } else {
            $relative_path = null;
        }

        return [
            'original_doc_name' => $original_filename,
            'doc_name' => $file_path_name,
            'path' => $relative_path,
            'slug' => Str::slug($filename) . '.' . $file_ext,
            'doc_type' => $file_ext,
        ];
    }

    public static function uploadFiles(array $files, $file_path = null, $disk = 'public')
    {
        $data = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $ab = self::uploadFile($file, $file_path, $disk);

                if ($ab['path'] != null) {
                    array_push($data, $ab['path']);
                }
            } else {
                array_push($data, $file);
            }
        }

        return ['data' => $data];
    }

    /**
     * Delete a file from storage
     *
     * @param  string|null  $filePath  The path to the file to delete
     * @param  string  $disk  The storage disk to use (default: 'public')
     * @return bool
     */
    public static function deleteFile($filePath, $disk = 'public')
    {
        if (! $filePath) {
            return false;
        }

        try {
            if (Storage::disk($disk)->exists($filePath)) {
                return Storage::disk($disk)->delete($filePath);
            }

            return true; // File doesn't exist, consider it "deleted"
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to delete file: ' . $filePath . ' - ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Delete multiple files from storage
     */
    public static function deleteFiles(array $filePaths): bool
    {
        $allDeleted = true;

        foreach ($filePaths as $filePath) {
            if (! self::deleteFile($filePath)) {
                $allDeleted = false;
            }
        }

        return $allDeleted;
    }
}
