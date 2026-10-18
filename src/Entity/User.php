<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\Id;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Table(name: 'pstryk_user')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'Konto z takim adresem e-mail już istnieje')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use Id;

    final public const string ROLE_USER = 'ROLE_USER';
    final public const string ROLE_ADMIN = 'ROLE_ADMIN';

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private string $email = '';

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'string')]
    private string $password = '';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    /**
     * @return non-empty-string
     */
    public function getUserIdentifier(): string
    {
        if ('' === $this->email) {
            throw new \LogicException('User e-mail address must not be empty.');
        }

        return $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = self::ROLE_USER;

        return array_values(array_unique($roles));
    }

    public function isAdmin(): bool
    {
        return \in_array(self::ROLE_ADMIN, $this->getRoles(), true);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    #[\Deprecated(message: 'Since Symfony 7.3, UserInterface::eraseCredentials() is deprecated and will be removed in 8.0', since: 'symfony/security-core 7.3')]
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
}
