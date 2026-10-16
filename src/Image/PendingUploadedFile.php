<?php declare(strict_types=1);

namespace App\Image;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * An image uploaded earlier by the drop zone: the original file plus the resized image and thumbnail
 * that were already generated for {@see $processedAlbum}, so saving the item does not have to convert it again.
 */
class PendingUploadedFile extends UploadedFile
{
    public function __construct(
        string $path,
        string $originalName,
        private readonly ?string $processedAlbum,
        private readonly string $processedImagePath,
        private readonly string $processedThumbPath,
        private readonly string $albumMarkerPath,
    ) {
        // "test" mode lets the file be moved with rename() instead of move_uploaded_file()
        parent::__construct($path, $originalName, 'image/jpeg', null, true);
    }

    /**
     * Converted files can be reused only for the album they were generated for (the album defines
     * sizes, watermark and tags) and only when both of them are still there.
     */
    public function isProcessedFor(?string $albumSlug): bool
    {
        return null !== $albumSlug
            && $this->processedAlbum === $albumSlug
            && is_file($this->processedImagePath)
            && is_file($this->processedThumbPath);
    }

    public function moveProcessed(string $imageTarget, string $thumbTarget): void
    {
        $this->relocate($this->processedImagePath, $imageTarget);
        $this->relocate($this->processedThumbPath, $thumbTarget);

        if (is_file($this->albumMarkerPath)) {
            unlink($this->albumMarkerPath);
        }
    }

    private function relocate(string $from, string $to): void
    {
        // rename() does not work between different file systems, e.g. mounted volumes
        if (@rename($from, $to)) {
            return;
        }

        if (!copy($from, $to)) {
            throw new \RuntimeException(\sprintf('Cannot move "%s" to "%s".', $from, $to));
        }

        unlink($from);
    }
}
