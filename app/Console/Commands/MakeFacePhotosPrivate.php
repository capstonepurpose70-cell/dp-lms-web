<?php

namespace App\Console\Commands;

use App\Support\FacePhotos;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * One-time move of face photos saved before they became private:
 * storage/app/public/faces/{id}/img_01.jpg → the private "faces" disk ({id}/img_01.jpg).
 * Safe to run again: a student who already has private photos is skipped, and the public
 * copy is removed only after every file was copied.
 */
class MakeFacePhotosPrivate extends Command
{
    protected $signature = 'faces:make-private';

    protected $description = 'Move face photos from the public disk to the private faces disk';

    public function handle(): int
    {
        $public = Storage::disk('public');
        if (! $public->exists('faces')) {
            $this->info('No public face photos found. Nothing to move.');
            return self::SUCCESS;
        }

        $moved = 0;
        foreach ($public->directories('faces') as $dir) {
            $id = basename($dir);
            if (! ctype_digit($id)) {
                // Leftover temporary upload folder (tmp_…): never a complete set
                $public->deleteDirectory($dir);
                continue;
            }

            if (! FacePhotos::has((int) $id)) {
                $copied = true;
                foreach ($public->files($dir) as $path) {
                    $name = basename($path);
                    if (FacePhotos::validName($name)) {
                        $copied = FacePhotos::disk()->put(FacePhotos::dir((int) $id) . '/' . $name, $public->get($path)) && $copied;
                    }
                }
                if (! $copied) {
                    $this->error("Could not copy every photo of student {$id}; the public copy was kept. Run the command again.");
                    $failed = true;
                    continue;
                }
                $moved++;
            }
            $public->deleteDirectory($dir);
        }

        if (! empty($failed)) {
            return self::FAILURE;
        }
        $public->deleteDirectory('faces');

        $this->info("Moved the photos of {$moved} student(s). The public faces folder is gone.");
        return self::SUCCESS;
    }
}
