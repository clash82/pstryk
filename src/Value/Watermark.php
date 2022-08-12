<?php declare(strict_types=1);

namespace App\Value;

use App\Exception\ArrayKeyNotExistsException;
use App\Value\Enum\WatermarkPosition;

class Watermark
{
    private bool $isEnabled;

    private string $file;

    private int $width;

    private int $height;

    private int $transparency;

    private int $position;

    private int $horizontalMargin;

    private int $verticalMargin;

    public function __construct(array $settings = [])
    {
        if (!isset($settings['watermark']['enabled'])) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new ArrayKeyNotExistsException('watermark:enabled');
        }
        $this->isEnabled = (bool) $settings['watermark']['enabled'];

        if (!isset($settings['watermark']['file'])) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new ArrayKeyNotExistsException('watermark:file');
        }
        $this->file = $settings['watermark']['file'];

        if (!isset($settings['watermark']['width'])) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new ArrayKeyNotExistsException('watermark:width');
        }
        $this->width = (int) $settings['watermark']['width'];

        if (!isset($settings['watermark']['height'])) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new ArrayKeyNotExistsException('watermark:height');
        }
        $this->height = (int) $settings['watermark']['height'];

        if (!isset($settings['watermark']['transparency'])) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new ArrayKeyNotExistsException('watermark:transparency');
        }
        $this->transparency = (int) $settings['watermark']['transparency'];

        if (!isset($settings['watermark']['position'])) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new ArrayKeyNotExistsException('watermark:position');
        }

        $this->position = WatermarkPosition::POSITION_BOTTOM_RIGHT;

        if ('top-left' === $settings['watermark']['position']) {
            $this->position = WatermarkPosition::POSITION_TOP_LEFT;
        }

        if ('top-right' === $settings['watermark']['position']) {
            $this->position = WatermarkPosition::POSITION_TOP_RIGHT;
        }

        if ('bottom-left' === $settings['watermark']['position']) {
            $this->position = WatermarkPosition::POSITION_BOTTOM_LEFT;
        }

        if (!isset($settings['watermark']['horizontal_margin'])) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new ArrayKeyNotExistsException('watermark:horizontal_margin');
        }
        $this->horizontalMargin = (int) $settings['watermark']['horizontal_margin'];

        if (!isset($settings['watermark']['vertical_margin'])) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new ArrayKeyNotExistsException('watermark:vertical_margin');
        }
        $this->verticalMargin = (int) $settings['watermark']['vertical_margin'];
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getTransparency(): int
    {
        return $this->transparency;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getHorizontalMargin(): int
    {
        return $this->horizontalMargin;
    }

    public function getVerticalMargin(): int
    {
        return $this->verticalMargin;
    }
}
