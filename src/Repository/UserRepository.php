<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Exception\RecordNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry, private readonly PaginatorInterface $paginator)
    {
        parent::__construct($registry, User::class);
    }

    public function getAllPaginated(int $page, int $usersPerPage): PaginationInterface
    {
        $query = $this
            ->createQueryBuilder('u')
            ->orderBy('u.email', 'ASC');

        return $this->paginator->paginate($query->getQuery(), $page, $usersPerPage);
    }

    public function getById(int $userId): User
    {
        $user = $this->find($userId);

        if (!$user instanceof User) {
            throw new RecordNotFoundException(\sprintf('No user found for id [%d]', $userId));
        }

        return $user;
    }

    public function countAdmins(): int
    {
        // roles are kept as JSON, so they are checked in PHP (there are only a few accounts anyway)
        $admins = 0;

        foreach ($this->findAll() as $user) {
            if ($user instanceof User && $user->isAdmin()) {
                ++$admins;
            }
        }

        return $admins;
    }

    public function update(User $user): void
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        $this->getEntityManager()->persist($user);
        /* @noinspection PhpUnhandledExceptionInspection */
        $this->getEntityManager()->flush();
    }

    public function delete(User $user): void
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        $this->getEntityManager()->remove($user);
        /* @noinspection PhpUnhandledExceptionInspection */
        $this->getEntityManager()->flush();
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(\sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }
}
