<?php

declare(strict_types=1);

namespace App\Modules\Members\Actions;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class StoreAvatar
{
    public function handle(User $user, UploadedFile $file): void
    {
        $contents = file_get_contents($file->getRealPath());
        $image = $contents === false ? false : @imagecreatefromstring($contents);

        if ($image === false) {
            throw ValidationException::withMessages([
                'avatar' => 'That image could not be read. Use a JPEG, PNG, or WebP file.',
            ]);
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $scale = min(512 / max($width, 1), 512 / max($height, 1), 1);
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));
        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $white);
        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        ob_start();
        imagejpeg($canvas, null, 82);
        $jpeg = ob_get_clean();
        imagedestroy($image);
        imagedestroy($canvas);

        if (! is_string($jpeg) || $jpeg === '') {
            throw ValidationException::withMessages([
                'avatar' => 'That image could not be saved.',
            ]);
        }

        $path = 'avatars/'.$user->id.'.jpg';
        Storage::disk('public')->put($path, $jpeg);

        $user->forceFill(['avatar_path' => $path])->save();
    }
}
