<?php

declare(strict_types=1);

namespace App\Manager;

use App\Exception\JsonResponseNotSetException;
use App\Provider\AdminSettingsProvider;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminSettingsManager
{
    /** @var JsonResponse */
    private $jsonResponse;

    public function setJsonResponse(JsonResponse $response): self
    {
        $this->jsonResponse = $response;

        return $this;
    }

    public function getJsonResponse(): JsonResponse
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        $this->checkIfResponseIsInitialized();

        return $this->jsonResponse;
    }

    public function setItemsPerPage(int $value): self
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        $this->checkIfResponseIsInitialized();

        $this->jsonResponse->headers->setCookie(
            new Cookie(AdminSettingsProvider::ITEM_FILTER_OPTIONS_ITEMS_PER_PAGE, (string) $value)
        );

        return $this;
    }

    public function setItemsSort(string $value): self
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        $this->checkIfResponseIsInitialized();

        $this->jsonResponse->headers->setCookie(
            new Cookie(AdminSettingsProvider::ITEM_FILTER_OPTIONS_ITEMS_SORT, $value)
        );

        return $this;
    }

    public function setItemsSortDirection(string $value): self
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        $this->checkIfResponseIsInitialized();

        $this->jsonResponse->headers->setCookie(
            new Cookie(AdminSettingsProvider::ITEM_FILTER_OPTIONS_ITEMS_SORT_DIRECTION, $value)
        );

        return $this;
    }

    public function setAlbum(string $value): self
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        $this->checkIfResponseIsInitialized();

        $this->jsonResponse->headers->setCookie(
            new Cookie(AdminSettingsProvider::ITEM_FILTER_OPTIONS_ALBUM, $value)
        );

        return $this;
    }

    private function checkIfResponseIsInitialized(): void
    {
        if (null === $this->jsonResponse) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new JsonResponseNotSetException();
        }
    }
}
