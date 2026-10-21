<?php

namespace App\Services;

use App\Exceptions\ActionNotAllowed;
use App\Models\FaceRegistration;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

/**
 * Pag-save ng face images (web camera + mobile app).
 *
 * Inayos: ang web ay binubura MUNA ang lumang photos bago i-save ang bago —
 * kapag pumalya sa gitna, nawawala ang approved photos na binabasa ng
 * Raspberry Pi. Ngayon iisa ang paraan: temp folder → swap.
 */
class FaceRegistrationService
{
    /** Pareho ang tsek sa web at API bago tumanggap ng images. */
    public function assertCanRegister(User $user): void
    {
        if ($user->studentSectionId() === null) {
            throw new ActionNotAllowed('You must be enrolled in a section before registering your face.');
        }

        if ($user->face_warnings >= 3) {
            throw new ActionNotAllowed('Face registration is blocked due to repeated violations. Please see the admin office.');
        }
    }

    /**
     * @param \Illuminate\Http\UploadedFile[] $files
     * @param bool $approved true = captured at the school kiosk with a staff-issued QR
     *                       (no admin review needed); false = self-upload, waits for admin
     * @return int ilang image ang na-save
     */
    public function replaceImages(User $user, array $files, bool $approved = false): int
    {
        $this->assertCanRegister($user);

        $disk     = Storage::disk('public');
        $finalDir = "faces/{$user->id}";
        $tmpDir   = "faces/tmp_{$user->id}_" . now()->format('YmdHisv');

        try {
            $count = 0;
            foreach (array_values($files) as $i => $file) {
                $file->storeAs($tmpDir, 'img_' . str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) . '.jpg', 'public');
                $count++;
            }

            // Saka lang buburahin ang luma kapag kumpleto na ang bago.
            $disk->deleteDirectory($finalDir);
            foreach ($disk->files($tmpDir) as $path) {
                $disk->move($path, "{$finalDir}/" . basename($path));
            }
            $disk->deleteDirectory($tmpDir);
        } catch (\Throwable $e) {
            $disk->deleteDirectory($tmpDir);
            report($e);
            throw new ActionNotAllowed('Could not save your photos. Please try again.', 500);
        }

        // Pinakabagong attempt lang ang itinatago.
        FaceRegistration::where('user_id', $user->id)->delete();
        FaceRegistration::create([
            'user_id'      => $user->id,
            'status'       => $approved ? 'approved' : 'pending',
            'images_count' => $count,
            'reviewed_at'  => $approved ? now() : null,
        ]);

        return $count;
    }
}
