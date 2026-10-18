<?php declare(strict_types=1);

namespace App\Manager;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserManager
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    /**
     * Stores the account; the password is changed only when a new (plain) one is given.
     */
    public function save(User $user, ?string $plainPassword = null): void
    {
        if (null !== $plainPassword && '' !== $plainPassword) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        }

        $this->userRepository->update($user);
    }

    /**
     * @throws \DomainException when the account must not be removed
     */
    public function deleteById(int $userId, ?User $currentUser): void
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        $user = $this->userRepository->getById($userId);

        if ($currentUser instanceof User && $currentUser->getId() === $user->getId()) {
            throw new \DomainException('Nie możesz usunąć własnego konta');
        }

        if ($user->isAdmin() && $this->userRepository->countAdmins() <= 1) {
            throw new \DomainException('Nie możesz usunąć ostatniego administratora');
        }

        $this->userRepository->delete($user);
    }
}
