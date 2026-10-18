<?php declare(strict_types=1);

namespace App\Provider;

use App\Entity\User;
use App\Repository\UserRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;

class UserProvider
{
    final public const int DEFAULT_PAGE_LIMIT = 20;

    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    public function getAllPaginated(int $page = 1, int $usersPerPage = self::DEFAULT_PAGE_LIMIT): PaginationInterface
    {
        return $this->userRepository->getAllPaginated($page, $usersPerPage);
    }

    public function getById(int $userId): User
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        return $this->userRepository->getById($userId);
    }

    public function countAdmins(): int
    {
        return $this->userRepository->countAdmins();
    }
}
