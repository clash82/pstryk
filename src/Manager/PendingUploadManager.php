<?php declare(strict_types=1);

namespace App\Manager;

use App\Image\ImageConverter;
use App\Image\PendingUploadedFile;
use App\Provider\AlbumProvider;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Keeps images uploaded one by one (by the drop zone) until the item form that references them is submitted.
 * Because every file travels in its own short request, neither max_file_uploads, post_max_size
 * nor max_input_time limit how many images can be added to an item. The resized image and the thumbnail
 * are generated right after the upload (see process()), so saving the item does not take longer
 * with every image added.
 */
class PendingUploadManager
{
    private const string TOKEN_PATTERN = '/^[a-f0-9]{32}$/';

    private const string EXTENSION = 'jpg';

    /** files never attached to a submitted form are removed after this time (in seconds) */
    private const int STALE_AFTER = 86400;

    private readonly string $directory;

    /**
     * @param string|null $directory defaults to a folder inside the system temp directory (sys_get_temp_dir())
     */
    public function __construct(
        private readonly ImageConverter $imageConverter,
        private readonly AlbumProvider $albumProvider,
        ?string $directory = null,
    ) {
        $this->directory = rtrim($directory ?? \sprintf('%s/pstryk_pending_uploads', sys_get_temp_dir()), '/');
    }

    /**
     * Stores an already validated file and returns a token that identifies it.
     */
    public function store(UploadedFile $file): string
    {
        $this->purgeStale();

        if (!is_dir($this->directory) && !mkdir($this->directory, 0700, true) && !is_dir($this->directory)) {
            throw new \RuntimeException(\sprintf('Cannot create directory "%s".', $this->directory));
        }

        $token = bin2hex(random_bytes(16));
        $file->move($this->directory, \sprintf('%s.%s', $token, self::EXTENSION));

        return $token;
    }

    /**
     * Generates the resized image and the thumbnail of the given album for the stored file. It can be called
     * again for the same token, e.g. when another album has been chosen in the meantime.
     *
     * @throws \InvalidArgumentException when the token or the album is unknown
     */
    public function process(string $token, string $albumSlug): void
    {
        if (!$this->exists($token)) {
            throw new \InvalidArgumentException('Nie znaleziono przesłanego pliku, dodaj go ponownie');
        }

        if (!$this->albumProvider->slugExists($albumSlug)) {
            throw new \InvalidArgumentException('Nieznany album');
        }

        // the marker is removed first, so a half finished conversion can never be taken for a finished one
        $marker = $this->getPath($token, 'album');

        if (is_file($marker)) {
            unlink($marker);
        }

        $this->imageConverter
            ->setAlbum($this->albumProvider->getBySlug($albumSlug))
            ->convertFile(
                $this->getPath($token, self::EXTENSION),
                $this->getPath($token, 'image.'.self::EXTENSION),
                $this->getPath($token, 'thumb.'.self::EXTENSION)
            );

        file_put_contents($marker, $albumSlug);
    }

    /**
     * Returns the stored file as an upload that can be handled like a regular one, or null
     * when the token is malformed or the file does not exist (anymore).
     */
    public function find(string $token): ?PendingUploadedFile
    {
        if (!$this->exists($token)) {
            return null;
        }

        $marker = $this->getPath($token, 'album');
        $album = is_file($marker) ? trim((string) file_get_contents($marker)) : null;

        return new PendingUploadedFile(
            $this->getPath($token, self::EXTENSION),
            \sprintf('%s.%s', $token, self::EXTENSION),
            $album,
            $this->getPath($token, 'image.'.self::EXTENSION),
            $this->getPath($token, 'thumb.'.self::EXTENSION),
            $marker
        );
    }

    public function purgeStale(): void
    {
        if (!is_dir($this->directory)) {
            return;
        }

        $threshold = time() - self::STALE_AFTER;

        foreach (glob(\sprintf('%s/*', $this->directory)) ?: [] as $path) {
            if (is_file($path) && filemtime($path) < $threshold) {
                unlink($path);
            }
        }
    }

    private function exists(string $token): bool
    {
        return 1 === preg_match(self::TOKEN_PATTERN, $token) && is_file($this->getPath($token, self::EXTENSION));
    }

    private function getPath(string $token, string $suffix): string
    {
        return \sprintf('%s/%s.%s', $this->directory, $token, $suffix);
    }
}
