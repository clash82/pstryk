<?php

declare(strict_types=1);

namespace App\Value;

use App\Exception\ArrayKeyNotExistsException;
use App\Value\Enum\WatermarkPosition;

class Watermark
{
    /** @var bool */
    private $isEnabled;

    /** @var string */
    private $file;

    /** @var int */
    private $width;

    /** @var int */
    private $height;

    /** @var int */
    private $transparency;

    /** @var int */
    private $position;

    /** @var int */
    private $horizontalMargin;

    /** @var int */
    private $verticalMargin;

    public function __construct(array $tags = [])
    {
        if (!isset($tags['enabled'])) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new ArrayKeyNotExistsException('enabled');
        }
        $this->isEnabled = (bool) $tags['enabled'];

        if (!isset($tags['file'])) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new ArrayKeyNotExistsException('file');
        }
        $this->file = $tags['file'];

        if (!isset($tags['width'])) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new ArrayKeyNotExistsException('width');
        }
        $this->width = (int) $tags['width'];

        if (!isset($tags['height'])) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new ArrayKeyNotExistsException('height');
        }
        $this->height = (int) $tags['height'];

        if (!isset($tags['transparency'])) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new ArrayKeyNotExistsException('transparency');
        }
        $this->transparency = (int) $tags['transparency'];

        if (!isset($tags['position'])) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new ArrayKeyNotExistsException('position');
        }

        $this->position = WatermarkPosition::POSITION_BOTTOM_RIGHT;

        if ('top-left' === $tags['position']) {
            $this->position = WatermarkPosition::POSITION_TOP_LEFT;
        }

        if ('top-right' === $tags['position']) {
            $this->position = WatermarkPosition::POSITION_TOP_RIGHT;
        }

        if ('bottom-left' === $tags['position']) {
            $this->position = WatermarkPosition::POSITION_BOTTOM_LEFT;
        }

        if (!isset($tags['horizontal_margin'])) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new ArrayKeyNotExistsException('horizontal_margin');
        }
        $this->horizontalMargin = (int) $tags['horizontal_margin'];

        if (!isset($tags['vertical_margin'])) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new ArrayKeyNotExistsException('vertical_margin');
        }
        $this->verticalMargin = (int) $tags['vertical_margin'];
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
