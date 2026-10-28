<?php

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Where student face photos live: the private "faces" disk, one folder per student
 * ({user id}/img_01.jpg …). Every reader and writer goes through here, so the photos are
 * never reachable through /storage and the file name rules sit in one place.
 */
final class FacePhotos
{
    public const DISK = 'faces';

    /** img_01.jpg … — what the kiosk and the upload form save, nothing else is ever served */
    private const NAME = '/^img_\d{2}\.(jpe?g|png)$/i';

    public static function disk(): Filesystem
    {
        return Storage::disk(self::DISK);
    }

    public static function dir(int $userId): string
    {
        return (string) $userId;
    }

    /** File names (img_01.jpg …) of the student's current set, in order. */
    public static function names(int $userId): array
    {
        $disk = self::disk();
        $dir  = self::dir($userId);
        if (! $disk->exists($dir)) {
            return [];
        }

        return collect($disk->files($dir))
            ->map(fn ($path) => basename($path))
            ->filter(fn ($name) => self::validName($name))
            ->sort()
            ->values()
            ->all();
    }

    public static function has(int $userId): bool
    {
        return self::names($userId) !== [];
    }

    public static function delete(int $userId): void
    {
        self::disk()->deleteDirectory(self::dir($userId));
    }

    public static function validName(string $name): bool
    {
        return (bool) preg_match(self::NAME, $name);
    }

    /** The photo as a private, uncached download — or null when it does not exist. */
    public static function response(int $userId, string $name): ?StreamedResponse
    {
        $path = self::dir($userId) . '/' . $name;
        if (! self::validName($name) || ! self::disk()->exists($path)) {
            return null;
        }

        return self::disk()->response($path, $name, [
            'Cache-Control'          => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
