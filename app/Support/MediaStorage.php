<?php

namespace App\Support;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class MediaStorage
{
    public static function diskName(): string
    {
        $disk = config('filesystems.media_disk', 'public');

        return is_string($disk) && $disk !== '' ? $disk : 'public';
    }

    public static function disk(): FilesystemAdapter
    {
        return Storage::disk(self::diskName());
    }

    public static function store(UploadedFile $file, string $directory): string
    {
        $path = self::disk()->putFile(trim($directory, '/'), $file);

        if (! is_string($path) || $path === '') {
            throw new RuntimeException('Nie udało się zapisać pliku w storage mediów.');
        }

        return $path;
    }

    public static function delete(?string $path): void
    {
        $path = self::normalizePath($path);

        if ($path !== null) {
            self::disk()->delete($path);
        }
    }

    public static function url(?string $path): ?string
    {
        $path = self::normalizePath($path);

        return $path === null ? null : self::disk()->url($path);
    }

    public static function normalizePath(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        $path = trim($path);

        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $parsedPath = parse_url($path, PHP_URL_PATH);
            $path = is_string($parsedPath) ? $parsedPath : $path;
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        return $path === '' ? null : $path;
    }
}
