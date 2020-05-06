<?php

declare(strict_types=1);

namespace App\Image;

trait UnsharpMaskTrait
{
    /**
     * Unsharp Mask for PHP - version 2.1.1.
     *
     * Unsharp mask algorithm by Torstein Hønsi 2003-07.
     * thoensi_at_netcom_dot_no. Please leave this notice.
     *
     * @param resource $image
     *
     * @return resource
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    private function applyUnsharpMask($image, float $amount, float $radius, float $threshold)
    {
        // image is an image that is already created within php using
        // imgcreatetruecolor. No url! $img must be a truecolor image.

        // Attempt to calibrate the parameters to Photoshop:
        if ($amount > 500) {
            $amount = 500;
        }
        $amount = $amount * 0.016;
        if ($radius > 50) {
            $radius = 50;
        }
        $radius = $radius * 2;
        if ($threshold > 255) {
            $threshold = 255;
        }

        $radius = abs(round($radius));     // Only integers make sense.
        if (empty($radius)) {
            return $image;
        }

        $w = imagesx($image);
        $h = imagesy($image);

        /** @var resource $imgCanvas */
        $imgCanvas = imagecreatetruecolor($w, $h);
        /** @var resource $imgBlur */
        $imgBlur = imagecreatetruecolor($w, $h);

        // Gaussian blur matrix:
        $matrix = [
            [1, 2, 1],
            [2, 4, 2],
            [1, 2, 1],
        ];
        imagecopy($imgBlur, $image, 0, 0, 0, 0, $w, $h);
        imageconvolution($imgBlur, $matrix, 16, 0);

        if ($threshold > 0) {
            // Calculate the difference between the blurred pixels and the original
            // and set the pixels
            for ($x = 0; $x < $w - 1; ++$x) {
                // each row
                for ($y = 0; $y < $h; ++$y) {
                    // each pixel
                    $rgbOrig = imagecolorat($image, $x, $y);
                    $rOrig = (($rgbOrig >> 16) & 0xFF);
                    $gOrig = (($rgbOrig >> 8) & 0xFF);
                    $bOrig = ($rgbOrig & 0xFF);

                    $rgbBlur = imagecolorat($imgBlur, $x, $y);
                    $rBlur = (($rgbBlur >> 16) & 0xFF);
                    $gBlur = (($rgbBlur >> 8) & 0xFF);
                    $bBlur = ($rgbBlur & 0xFF);

                    // When the masked pixels differ less from the original
                    // than the threshold specifies, they are set to their original value.
                    $rNew = (abs($rOrig - $rBlur) >= $threshold)
                        ? max(0, min(255, ($amount * ($rOrig - $rBlur)) + $rOrig))
                        : $rOrig;

                    $gNew = (abs($gOrig - $gBlur) >= $threshold)
                        ? max(0, min(255, ($amount * ($gOrig - $gBlur)) + $gOrig))
                        : $gOrig;

                    $bNew = (abs($bOrig - $bBlur) >= $threshold)
                        ? max(0, min(255, ($amount * ($bOrig - $bBlur)) + $bOrig))
                        : $bOrig;

                    if (($rOrig !== $rNew) || ($gOrig !== $gNew) || ($bOrig !== $bNew)) {
                        $pixCol = imagecolorallocate($image, (int) $rNew, (int) $gNew, (int) $bNew);
                        imagesetpixel($image, $x, $y, $pixCol);
                    }
                }
            }
        } else {
            for ($x = 0; $x < $w; ++$x) {
                // each row
                for ($y = 0; $y < $h; ++$y) {
                    // each pixel
                    $rgbOrig = imagecolorat($image, $x, $y);
                    $rOrig = (($rgbOrig >> 16) & 0xFF);
                    $gOrig = (($rgbOrig >> 8) & 0xFF);
                    $bOrig = ($rgbOrig & 0xFF);

                    $rgbBlur = imagecolorat($imgBlur, $x, $y);

                    $rBlur = (($rgbBlur >> 16) & 0xFF);
                    $gBlur = (($rgbBlur >> 8) & 0xFF);
                    $bBlur = ($rgbBlur & 0xFF);

                    $rNew = ($amount * ($rOrig - $rBlur)) + $rOrig;
                    if ($rNew > 255) {
                        $rNew = 255;
                    } elseif ($rNew < 0) {
                        $rNew = 0;
                    }

                    $gNew = ($amount * ($gOrig - $gBlur)) + $gOrig;
                    if ($gNew > 255) {
                        $gNew = 255;
                    } elseif ($gNew < 0) {
                        $gNew = 0;
                    }

                    $bNew = ($amount * ($bOrig - $bBlur)) + $bOrig;

                    if ($bNew > 255) {
                        $bNew = 255;
                    } elseif ($bNew < 0) {
                        $bNew = 0;
                    }

                    /** @var int $rgbNew */
                    $rgbNew = ($rNew << 16) + ($gNew << 8) + $bNew;
                    imagesetpixel($image, $x, $y, $rgbNew);
                }
            }
        }

        imagedestroy($imgCanvas);
        imagedestroy($imgBlur);

        return $image;
    }
}
