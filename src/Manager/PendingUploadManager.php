<?php declare(strict_types=1);

namespace App\Manager;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Keeps images uploaded one by one (by the drop zone) until the item form that references them is submitted.
 * Because every file travels in its own short request, neither max_file_uploads, post_max_size
 * nor max_input_time limit how many images can be added to an item.
 */
class PendingUploadManager
{
    private const string TOKEN_PATTERN = '/^[a-f0-9]{32}$/';

    private const string EXTENSION = 'jpg';

    /** files never attached to a submitted form are removed after this time (in seconds) */
    private const int STALE_AFTER = 86400;

    public function __construct(
        #[Autowire('%kernel.project_dir%/var/pending_uploads')]
        private readonly string $directory,
    ) {
    }

    /**
     * Stores an already validated file and returns a token that identifies it.
     */
    public function store(UploadedFile $file): string
    {
        $this->purgeStale();

        if (!is_dir($this->directory) && !mkdir($this->directory, 0775, true) && !is_dir($this->directory)) {
            throw new \RuntimeException(\sprintf('Cannot create directory "%s".', $this->directory));
        }

        $token = bin2hex(random_bytes(16));
        $file->move($this->directory, $this->getFilename($token));

        return $token;
    }

    /**
     * Returns the stored file as an upload that can be handled like a regular one, or null
     * when the token is malformed or the file does not exist (anymore).
     */
    public function find(string $token): ?UploadedFile
    {
        if (1 !== preg_match(self::TOKEN_PATTERN, $token)) {
            return null;
        }

        $path = \sprintf('%s/%s', $this->directory, $this->getFilename($token));

        if (!is_file($path)) {
            return null;
        }

        // "test" mode lets the file be moved with rename() instead of move_uploaded_file()
        return new UploadedFile($path, $this->getFilename($token), 'image/jpeg', null, true);
    }

    public function purgeStale(): void
    {
        if (!is_dir($this->directory)) {
            return;
        }

        $threshold = time() - self::STALE_AFTER;

        foreach (glob(\sprintf('%s/*.%s', $this->directory, self::EXTENSION)) ?: [] as $path) {
            if (is_file($path) && filemtime($path) < $threshold) {
                unlink($path);
            }
        }
    }

    private function getFilename(string $token): string
    {
        return \sprintf('%s.%s', $token, self::EXTENSION);
    }
}
